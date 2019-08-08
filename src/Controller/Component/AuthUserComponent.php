<?php

declare(strict_types=1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace App\Controller\Component;

use App\Controller\AppController;
use App\Model\Table\UsersTable;
use Cake\Controller\Component;
use Cake\Controller\Component\AuthComponent;
use Cake\Controller\Controller;
use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\ORM\TableRegistry;
use Firebase\JWT\JWT;
use Saito\App\Registry;
use Saito\User\Cookie\CurrentUserCookie;
use Saito\User\Cookie\Storage;
use Saito\User\CurrentUser\CurrentUserFactory;
use Saito\User\CurrentUser\CurrentUserInterface;
use Stopwatch\Lib\Stopwatch;

/**
 * Authenticates the current user and bootstraps the CurrentUser information
 *
 * @property AuthComponent $Auth
 */
class AuthUserComponent extends Component
{
    /**
     * Component name
     *
     * @var string
     */
    public $name = 'CurrentUser';

    /**
     * Component's components
     *
     * @var array
     */
    public $components = ['Auth', 'Cron.Cron'];

    /**
     * Current user
     *
     * @var CurrentUserInterface
     */
    protected $CurrentUser;

    /**
     * UsersTableInstance
     *
     * @var UsersTable
     */
    protected $UsersTable = null;

    /**
     * {@inheritDoc}
     */
    public function initialize(array $config)
    {
        Stopwatch::start('CurrentUser::initialize()');

        /** @var UsersTable */
        $UsersTable = TableRegistry::getTableLocator()->get('Users');
        $this->UsersTable = $UsersTable;

        $this->initSessionAuth($this->Auth);

        if ($this->isBot()) {
            $CurrentUser = CurrentUserFactory::createDummy();
        } else {
            $user = $this->_login();
            $controller = $this->getController();
            $isLoggedIn = !empty($user);

            /// don't auto-login on login related pages
            $excluded = ['login', 'register'];
            $useLoggedIn = $isLoggedIn
                && !in_array($controller->getRequest()->getParam('action'), $excluded);

            if ($useLoggedIn) {
                $CurrentUser = CurrentUserFactory::createLoggedIn($user);
                $userId = (string)$CurrentUser->getId();
            } else {
                $CurrentUser = CurrentUserFactory::createVisitor($controller);
                $userId = $this->request->getSession()->id();
            }

            $this->UsersTable->UserOnline->setOnline($userId, $useLoggedIn);
        }

        $this->setCurrentUser($CurrentUser);

        Stopwatch::stop('CurrentUser::initialize()');
    }

    /**
     * Detects if the current user is a bot
     *
     * @return bool
     */
    public function isBot()
    {
        return $this->request->is('bot');
    }

    /**
     * Tries to log-in a user
     *
     * Call this from controllers to authenticate manually (from login-form-data).
     *
     * @return bool Was login successfull?
     */
    public function login(): bool
    {
        // destroy any existing session or auth-data
        $this->logout();

        // non-logged in session-id is lost after Auth::setUser()
        $originalSessionId = session_id();

        $user = $this->_login();

        if (empty($user)) {
            // login failed
            return false;
        }

        //// Login succesfull

        $user = $this->UsersTable->get($user['id']);

        $CurrentUser = CurrentUserFactory::createLoggedIn($user->toArray());
        $this->setCurrentUser($CurrentUser);

        $this->UsersTable->incrementLogins($user);
        $this->UsersTable->UserOnline->setOffline($originalSessionId);

        /// password update
        $password = (string)$this->request->getData('password');
        if ($password) {
            $this->UsersTable->autoUpdatePassword($this->CurrentUser->getId(), $password);
        }

        /// set persistent Cookie
        $setCookie = (bool)$this->request->getData('remember_me');
        if ($setCookie) {
            (new CurrentUserCookie($this->getController()))->write($this->CurrentUser->getId());
        };

        return true;
    }

    /**
     * Tries to login the user.
     *
     * @return null|array if user is logged-in null otherwise
     */
    protected function _login(): ?array
    {
        // Check if AuthComponent knows user from session-storage (usually
        // compare session-cookie)
        // Notice: Will hit session storage. Usually files.
        $user = $this->Auth->user();

        if (!$user) {
            // Check if user is authenticated via one of the Authenticators
            // (cookie, token, …).
            // Notice: Authenticators may hit DB to find user
            $user = $this->Auth->identify();

            if (!empty($user)) {
                // set user in session-storage to be available in subsequent requests
                // Notice: on write Cake 3 will start a new session (new session-id)
                $this->Auth->setUser($user);
            }
        }

        if (empty($user)) {
            // Authentication failed.
            return null;
        }

        // Session-data may be outdated. Make sure that user-data is up-to-date:
        // user not locked/user-type wasn't changend/… since session-storage was written.
        // Notice: is going to hit DB
        Stopwatch::start('CurrentUser read user from DB');
        $user = $this->UsersTable
            ->find('allowedToLogin')
            ->where(['id' => $user['id']])
            ->first();
        Stopwatch::stop('CurrentUser read user from DB');

        if (empty($user)) {
            /// no user allowed to login
            // destroy any existing (session) storage information
            $this->logout();
            // send to logout form for formal logout procedure
            $this->getController()->redirect(['_name' => 'logout']);

            return null;
        }

        return $user->toArray();
    }

    /**
     * Logs-out user: clears session data and cookies.
     *
     * @return void
     */
    public function logout(): void
    {
        if (!empty($this->CurrentUser)) {
            if ($this->CurrentUser->isLoggedIn()) {
                $this->UsersTable->UserOnline->setOffline($this->CurrentUser->getId());
            }
            $this->setCurrentUser(CurrentUserFactory::createVisitor($this->getController()));
        }
        $this->Auth->logout();
    }

    /**
     * Configures CakePHP's authentication-component
     *
     * @param AuthComponent $auth auth-component to configure
     * @return void
     */
    public function initSessionAuth(AuthComponent $auth): void
    {
        if ($auth->getConfig('authenticate')) {
            // different auth configuration already in place (e.g. API)
            return;
        };

        $auth->setConfig(
            'authenticate',
            [
                AuthComponent::ALL => ['finder' => 'allowedToLogin'],
                'Cookie',
                'Mlf',
                'Mlf2',
                'Form'
            ]
        );

        $auth->setConfig('authorize', ['Controller']);
        $auth->setConfig('loginAction', '/login');

        $here = urlencode($this->getController()->getRequest()->getRequestTarget());
        $auth->setConfig('unauthorizedRedirect', '/login?redirect=' . $here);

        $auth->deny();
        $auth->setConfig('authError', __('authentication.error'));
    }

    /**
     * {@inheritDoc}
     */
    public function shutdown(Event $event)
    {
        $this->setJwtCookie($event->getSubject());
    }

    /**
     * Sets (or deletes) the JS-Web-Token in Cookie for access in front-end
     *
     * @param Controller $controller The controller
     * @return void
     */
    private function setJwtCookie(Controller $controller): void
    {
        $cookieKey = Configure::read('Session.cookie') . '-jwt';
        $cookie = (new Storage($controller, $cookieKey, ['http' => false, 'expire' => '+1 week']));

        $existingToken = $cookie->read();

        // user not logged-in: no JWT-cookie for you
        if (!$this->CurrentUser->isLoggedIn()) {
            if ($existingToken) {
                $cookie->delete();
            }

            return;
        }

        if ($existingToken) {
            //// check that token belongs to current-user
            $parts = explode('.', $existingToken);
            // [performance] Done every logged-in request. Don't decrypt whole token with signature.
            // We only make sure it exists, the auth happens elsewhere.
            $payload = Jwt::jsonDecode(Jwt::urlsafeB64Decode($parts[1]));
            if ($payload->sub === $this->CurrentUser->getId() && $payload->exp > time()) {
                return;
            }
        }

        // use easy to change cookieSalt to allow emergency invalidation of all existing tokens
        $jwtKey = Configure::read('Security.cookieSalt');
        // cookie expires before JWT (7 days < 14 days): JWT exp should always be valid
        $jwtPayload = ['sub' => $this->CurrentUser->getId(), 'exp' => time() + (86400 * 14)];
        $jwtToken = \Firebase\JWT\JWT::encode($jwtPayload, $jwtKey);
        $cookie->write($jwtToken);
    }

    /**
     * Returns the current-user
     *
     * @return CurrentUserInterface
     */
    public function getUser(): CurrentUserInterface
    {
        return $this->CurrentUser;
    }

    /**
     * Makes the current user available throughout the application
     *
     * @param CurrentUserInterface $CurrentUser current-user to set
     * @return void
     */
    private function setCurrentUser(CurrentUserInterface $CurrentUser): void
    {
        $this->CurrentUser = $CurrentUser;

        /** @var AppController */
        $controller = $this->getController();
        // makes CurrentUser available in Controllers
        $controller->CurrentUser = $this->CurrentUser;
        // makes CurrentUser available as View var in templates
        $controller->set('CurrentUser', $this->CurrentUser);
        Registry::set('CU', $this->CurrentUser);
    }
}
