<?php
/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers 2015
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace App\Controller\Component;

use App\Model\Table\UsersTable;
use Cake\Controller\Component;
use Cake\Controller\Component\AuthComponent;
use Cake\Controller\Controller;
use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\ORM\TableRegistry;
use Cake\Routing\Router;
use Cake\Utility\Security;
use Firebase\JWT\JWT;
use Saito\App\Registry;
use Saito\User\Categories;
use Saito\User\Cookie\CurrentUserCookie;
use Saito\User\Cookie\Storage;
use Saito\User\CurrentUser\CurrentUserInterface;
use Saito\User\CurrentUser\CurrentUserTrait;
use Saito\User\LastRefresh;
use Saito\User\ReadPostings;
use Saito\User\ReadPostings\ReadPostingsCookie;
use Saito\User\ReadPostings\ReadPostingsDatabase;
use Saito\User\ReadPostings\ReadPostingsDummy;
use Saito\User\SaitoUserTrait;
use Stopwatch\Lib\Stopwatch;

/**
 * Class CurrentUserComponent
 *
 * @package App\Controller\Component
 * @property AuthComponent $Auth
 */
class CurrentUserComponent extends Component implements CurrentUserInterface
{
    use CurrentUserTrait;
    use SaitoUserTrait;

    /**
     * @var \Saito\User\Categories
     */
    public $Categories;

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
     * Manages the last refresh/mark entries as read for the current user
     *
     * @var \Saito\User\LastRefresh\LastRefreshAbstract
     */
    public $LastRefresh = null;

    /**
     * @var ReadPostingsDatabase
     */
    public $ReadEntries;

    /**
     * Model User instance exclusive to the CurrentUserComponent
     *
     * @var UsersTable
     */
    protected $_User = null;

    /**
     * {@inheritDoc}
     */
    public function initialize(array $config)
    {
        Registry::set('CU', $this);

        $this->Categories = new Categories($this);
        $this->_User = TableRegistry::get('Users');

        $this->configureAuthentication($this->Auth);

        // don't auto-login on login related pages
        $excluded = ['login', 'register'];
        if (!in_array($this->request->getParam('action'), $excluded)) {
            $this->_login();
        }

        if ($this->isLoggedIn()) {
            $this->LastRefresh = new LastRefresh\LastRefreshDatabase($this);
            $storage = TableRegistry::get('UserReads');
            $this->ReadEntries = new ReadPostingsDatabase($this, $storage);
        } elseif ($this->isBot()) {
            $this->LastRefresh = new LastRefresh\LastRefreshDummy();
            $this->ReadEntries = new ReadPostingsDummy();
        } else {
            $this->LastRefresh = new LastRefresh\LastRefreshCookie($this);
            $storage = new Storage($this->getController(), 'Saito-Read');
            $this->ReadEntries = new ReadPostingsCookie($this, $storage);
        }

        $this->_markOnline();
    }

    /**
     * Marks users as online
     *
     * @return void
     */
    protected function _markOnline()
    {
        Stopwatch::start('CurrentUser->_markOnline()');
        $isLoggedIn = $this->isLoggedIn();
        if ($isLoggedIn) {
            $userId = $this->getId();
        } else {
            // don't count search bots as guests
            if ($this->isBot()) {
                return;
            }
            $userId = $this->request->getSession()->id();
        }
        $this->_User->UserOnline->setOnline($userId, $isLoggedIn);
        Stopwatch::stop('CurrentUser->_markOnline()');
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
     * Tries to authenticate a user provided by credentials in request (usually form-data)
     *
     * @return bool Was login successfull
     */
    public function login(): bool
    {
        // destroy any existing session or auth-data
        $this->logout();

        // non-logged in session-id is lost after Auth::setUser()
        $originalSessionId = session_id();

        $user = $this->_login();

        if (empty($user)) {
            return false;
        }

        $user = $this->_User->get($this->getId());
        $this->_User->incrementLogins($user);
        $this->_User->UserOnline->setOffline($originalSessionId);

        //= password update
        $password = $this->request->getData('password');
        if ($password) {
            $this->_User->autoUpdatePassword($this->getId(), $password);
        }

        //= set persistent Cookie
        $setCookie = (bool)$this->request->getData('remember_me');
        if ($setCookie) {
            (new CurrentUserCookie($this->getController()))->write($this->getId());
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
        // check if AuthComponent knows user from session-storage (usually session)
        // Notice: will hit session storage (usually files)
        $user = $this->Auth->user();

        if ($user) {
            // Session-data may be outdated. Make sure that user-data is up-to-date:
            // user not locked/user-type wasn't changend/… since session-storage was written.
            // Notice: is going to hit DB
            $user = $this->_User->findAllowedToLoginById($user['id'])
                ->first();

            if (empty($user)) {
                //// no user allowed to login found
                // destroy the invalid session storage information
                $this->logout();
                // send to logout form for formal logout procedure
                $this->getController()->redirect(['_name' => 'logout']);

                return null;
            }

            $user = $user->toArray();
        } else {
            // Check if user is authable via one of the Authenticators (cookie, token, …).
            // Notice: Authenticators may hit DB to find user
            $user = $this->Auth->identify();

            if (!empty($user)) {
                // set user in session-storage to be available in subsequent requests
                // Notice: on write Cake 3 will start a new session (new session-id)
                $this->Auth->setUser($user);
            }
        }

        if (empty($user)) {
            return null;
        }

        $this->setSettings($user);

        return $user;
    }

    /**
     * Logs-out user: clears session data and cookies.
     *
     * @return void
     */
    public function logout(): void
    {
        if ($this->isLoggedIn()) {
            $this->_User->UserOnline->setOffline($this->getId());
        }
        $this->setSettings(null);
        $this->Auth->logout();
    }

    /**
     * {@inheritDoc}
     */
    public function beforeRender()
    {
        // write out the current user for access in the views
        $this->getController()->set('CurrentUser', $this);
    }

    /**
     * Get user-model
     *
     * @return UsersTable
     */
    public function getModel()
    {
        return $this->_User;
    }

    /**
     * Configures the auth component
     *
     * @param AuthComponent $auth auth-component to configure
     * @return void
     */
    protected function configureAuthentication(AuthComponent $auth): void
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

        if ($this->isLoggedIn()) {
            $auth->allow();
        } else {
            $auth->deny();
        }
        $auth->setConfig('authError', __('auth_autherror'));
    }

    /**
     * {@inheritDoc}
     */
    public function startup(Event $event)
    {
        $this->validateUser($event->getSubject());
    }

    /**
     * {@inheritDoc}
     */
    public function shutdown(Event $event)
    {
        $this->setJwtCookie($event->getSubject());
    }

    /**
     * Checks if user is valid and logs him out if not
     *
     * @param Controller $controller The controller
     * @return void
     */
    protected function validateUser(Controller $controller): void
    {
        if (!$this->isLoggedIn()) {
            return;
        }
        if (!$this->isForbidden()) {
            return;
        }
        if ($controller->getRequest()->getParam('action') === 'logout') {
            return;
        }
        $this->getController()->redirect(['_name' => 'logout']);
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
        if (!$this->isLoggedIn()) {
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
            if ($payload->sub === $this->getId() && $payload->exp > time()) {
                return;
            }
        }

        // use easy to change cookieSalt to allow emergency invalidation of all existing tokens
        $jwtKey = Configure::read('Security.cookieSalt');
        // cookie expires before JWT (7 days < 14 days): JWT exp should always be valid
        $jwtPayload = ['sub' => $this->getId(), 'exp' => time() + (86400 * 14)];
        $jwtToken = \Firebase\JWT\JWT::encode($jwtPayload, $jwtKey);
        $cookie->write($jwtToken);
    }
}
