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
use App\Model\Entity\User;
use App\Model\Table\UsersTable;
use Authentication\Authenticator\CookieAuthenticator;
use Authentication\Controller\Component\AuthenticationComponent;
use Authentication\Identifier\PasswordIdentifier;
use Cake\Controller\Component;
use Cake\Controller\Controller;
use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\Http\Exception\ForbiddenException;
use Cake\ORM\TableRegistry;
use Firebase\JWT\JWT;
use Saito\App\Registry;
use Saito\User\Cookie\Storage;
use Saito\User\CurrentUser\CurrentUser;
use Saito\User\CurrentUser\CurrentUserFactory;
use Saito\User\CurrentUser\CurrentUserInterface;
use Stopwatch\Lib\Stopwatch;

/**
 * Authenticates the current user and bootstraps the CurrentUser information
 *
 * @property AuthenticationComponent $Authentication
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
    public $components = [
        'ActionAuthorization',
        'Authentication',
        // TODO Check why Cron is used here
        'Cron.Cron'
    ];

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

        if ($this->isBot()) {
            $CurrentUser = CurrentUserFactory::createDummy();
        } else {
            $user = $this->authenticate();
            $isLoggedIn = !empty($user);

            $controller = $this->getController();
            $request = $controller->getRequest();
            /// don't auto-login on login related pages
            $excluded = ['login', 'register'];
            $useLoggedIn = $isLoggedIn
                && !in_array($request->getParam('action'), $excluded);

            if ($useLoggedIn) {
                $CurrentUser = CurrentUserFactory::createLoggedIn($user->toArray());
                $userId = (string)$CurrentUser->getId();
            } else {
                $CurrentUser = CurrentUserFactory::createVisitor($controller);
                $userId = $request->getSession()->id();
            }

            $this->UsersTable->UserOnline->setOnline($userId, $useLoggedIn);
        }

        $this->setCurrentUser($CurrentUser);

        if(!$this->isAuthorized($this->CurrentUser)) {
            throw new ForbiddenException();
        }

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
        // destroy any existing session or Authentication-data
        $this->logout();

        // non-logged in session-id is lost after Authentication
        $originalSessionId = session_id();

        $user = $this->authenticate();

        if (!$user) {
            // login failed
            return false;
        }

        $this->Authentication->setIdentity($user);
        $CurrentUser = CurrentUserFactory::createLoggedIn($user->toArray());
        $this->setCurrentUser($CurrentUser);

        $this->UsersTable->incrementLogins($user);
        $this->UsersTable->UserOnline->setOffline($originalSessionId);

        /// password update
        $authenticationService = $this->Authentication->getAuthenticationService();
        /** @var PasswordIdentifier */
        $identifier = $authenticationService->identifiers()->get('Password');
        if ($identifier->needsPasswordRehash()) {
            $user = $this->UsersTable->get($user->get('id'));
            $user->set('password', $this->request->getData('password'));
            $this->UsersTable->save($user);
        }

        return true;
    }

    /**
     * Tries to authenticate and login the user.
     *
     * @return null|User User if is logged-in, null otherwise.
     */
    protected function authenticate(): ?User
    {
        $result = $this->Authentication->getResult();

        $loginFailed = !$result->isValid();
        if ($loginFailed) {
            return null;
        }

        $user = $result->getData();

        $allowed = $user['activate_code'] === 0 && $user['user_lock'] === false;

        if (!$allowed) {
            /// User isn't allowed to be logged-in
            // Destroy any existing (session) storage information.
            $this->logout();
            // Send to logout-form for formal logout procedure.
            $this->getController()->redirect(['_name' => 'logout']);

            return null;
        }

        $this->refreshAuthenticationProvider();

        return $user;
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
        $this->Authentication->logout();
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
            // Different auth configuration already in place (e.g. API). This is
            // important for the JWT-request, so that we don't authenticate via
            // Cookie and open up for xsrf issues.
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
     * Update persistent authentication providers for regular visitors.
     *
     * Users who visit somewhat regularly shall not be logged-out.
     *
     * @return void
     */
    private function refreshAuthenticationProvider()
    {
        // Get current authentication provider
        $authenticationProvider = $this->Authentication
            ->getAuthenticationService()
            ->getAuthenticationProvider();

        // Persistent login provider is cookie based. Every time that cookie is
        // used for a login its expiry is pushed forward.
        if ($authenticationProvider instanceof CookieAuthenticator) {
            $controller = $this->getController();

            $cookieKey = $authenticationProvider->getConfig('cookie.name');
            $cookie = $controller->getRequest()->getCookieCollection()->get($cookieKey);
            if (empty($cookieKey) || empty($cookie)) {
                throw new \RuntimeException(
                    sprintf('Auth-cookie "%s" not found for refresh.', $cookieKey),
                    1569739698
                );
            }

            $expire = $authenticationProvider->getConfig('cookie.expire');
            $refreshedCookie = $cookie->withExpiry($expire);

            $response = $controller->getResponse()->withCookie($refreshedCookie);
            $controller->setResponse($response);
        }
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

    /**
     * Check if user is authorized to access the current action.
     *
     * @param CurrentUser $user The current user.
     * @return bool True if authorized False otherwise.
     */
    private function isAuthorized(CurrentUser $user)
    {
        $controller = $this->getController();
        $action = $controller->getRequest()->getParam('action');

        if (isset($controller->actionAuthConfig)
            && isset($controller->actionAuthConfig[$action])) {
            $requiredRole = $controller->actionAuthConfig[$action];

            return Registry::get('Permission')
                ->check($user->getRole(), $requiredRole);
        }

        $prefix = $this->request->getParam('prefix');
        $plugin = $this->request->getParam('plugin');
        $isAdminRoute = ($prefix && strtolower($prefix) === 'admin')
            || ($plugin && strtolower($plugin) === 'admin');
        if ($isAdminRoute) {
            return $user->permission('saito.core.admin.backend');
        }

        return true;
    }
}
