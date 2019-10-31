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
use Cake\Controller\Component;
use Cake\Controller\Controller;
use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\Http\Exception\ForbiddenException;
use Cake\ORM\TableRegistry;
use DateTimeImmutable;
use Firebase\JWT\JWT;
use Saito\RememberTrait;
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
    use RememberTrait;

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
        'Authentication.Authentication',
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
     * Array of authorized actions 'action' => 'resource'
     *
     * @var array
     */
    private $actionAuthorizationResources = [];

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
            $controller = $this->getController();
            $request = $controller->getRequest();

            $user = $this->authenticate();
            if (!empty($user)) {
                $CurrentUser = CurrentUserFactory::createLoggedIn($user->toArray());
                $userId = (string)$CurrentUser->getId();
                $isLoggedIn = true;
            } else {
                $CurrentUser = CurrentUserFactory::createVisitor($controller);
                $userId = $request->getSession()->id();
                $isLoggedIn = false;
            }

            $this->UsersTable->UserOnline->setOnline($userId, $isLoggedIn);
        }

        $this->setCurrentUser($CurrentUser);

        Stopwatch::stop('CurrentUser::initialize()');
    }

    /**
     * {@inheritDoc}
     */
    public function startup()
    {
        if (!$this->isAuthorized($this->CurrentUser)) {
            throw new ForbiddenException(null, 1571852880);
        }
    }

    /**
     * Detects if the current user is a bot
     *
     * @return bool
     */
    public function isBot()
    {
        return $this->remember('isBot', $this->getController()->getRequest()->is('bot'));
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
        $password = (string)$this->request->getData('password');
        if ($password) {
            $this->UsersTable->autoUpdatePassword($this->CurrentUser->getId(), $password);
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

        /** @var User User is always retrieved from ORM */
        $user = $result->getData();

        $isUnactivated = $user['activate_code'] !== 0;
        $isLocked = $user['user_lock'] == true;

        if ($isUnactivated || $isLocked) {
            /// User isn't allowed to be logged-in
            // Destroy any existing (session) storage information.
            $this->logout();

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
     * Stores (or deletes) the JS-Web-Token as Cookie for access in front-end
     *
     * @param Controller $controller The controller
     * @return void
     */
    private function setJwtCookie(Controller $controller): void
    {
        $expire = '+1 day';
        $cookieKey = Configure::read('Session.cookie') . '-JWT';
        $cookie = new Storage(
            $controller,
            $cookieKey,
            ['http' => false, 'expire' => $expire]
        );

        $existingToken = $cookie->read();

        // User not logged-in: No JWT-cookie for you!
        if (!$this->CurrentUser->isLoggedIn()) {
            if ($existingToken) {
                $cookie->delete();
            }

            return;
        }

        if ($existingToken) {
            // Encoded JWT token format: <header>.<payload>.<signature>
            $parts = explode('.', $existingToken);
            $payloadEncoded = $parts[1];
            // [performance] Done every logged-in request. Don't decrypt whole
            // token with signature. We only make sure it exists, the auth
            // happens elsewhere.
            $payload = Jwt::jsonDecode(Jwt::urlsafeB64Decode($payloadEncoded));
            $isCurrentUser = $payload->sub === $this->CurrentUser->getId();
            // Assume expired if within the next two hours.
            $aboutToExpire = $payload->exp > (time() - 7200);
            // Token doesn't require an update if it belongs to current user and
            // isn't about to expire.
            if ($isCurrentUser && !$aboutToExpire) {
                return;
            }
        }

        /// Set new token
        // Use easy to change cookieSalt to allow emergency invalidation of all
        // existing tokens.
        $jwtKey = Configure::read('Security.cookieSalt');
        $jwtPayload = [
            'sub' => $this->CurrentUser->getId(),
            // Token is valid for one day.
            'exp' => (new DateTimeImmutable($expire))->getTimestamp(),
        ];
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
    }

    /**
     * The controller action will be authorized with a permission resource.
     *
     * @param string $action The controller action to authorize.
     * @param string $resource The permission resource token.
     * @return void
     */
    public function authorizeAction(string $action, string $resource)
    {
        $this->actionAuthorizationResources[$action] = $resource;
    }

    /**
     * Check if user is authorized to access the current action.
     *
     * @param CurrentUser $user The current user.
     * @return bool True if authorized False otherwise.
     */
    private function isAuthorized(CurrentUser $user)
    {
        /// Authorize action through resource
        $action = $this->getController()->getRequest()->getParam('action');
        if (isset($this->actionAuthorizationResources[$action])) {
            return $user->permission($this->actionAuthorizationResources[$action]);
        }

        /// Authorize admin area
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
