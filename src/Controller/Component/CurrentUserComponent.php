<?php
/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers 2015
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace App\Controller\Component;

use App\Model\Table\UserReadsTable;
use App\Model\Table\UsersTable;
use Cake\Controller\Component;
use Cake\Controller\Component\AuthComponent;
use Cake\Controller\Controller;
use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\ORM\TableRegistry;
use Cake\Routing\Router;
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
use \Stopwatch\Lib\Stopwatch;

/**
 * Class CurrentUserComponent
 *
 * @package App\Controller\Component
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
    public $components = ['Cookie', 'Cron.Cron'];

    /**
     * Manages the persistent login cookie
     *
     * @var \Saito\User\Cookie\CurrentUserCookie
     */
    public $PersistentCookie = null;

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
     * Reference to the controller
     *
     * @var Controller
     */
    protected $_Controller = null;

    /**
     * {@inheritDoc}
     */
    public function initialize(array $config)
    {
        $Controller = $this->_registry->getController();
        $this->_Controller = $Controller;
        Registry::set('CU', $this);

        $this->Categories = new Categories($this);
        $this->_User = TableRegistry::get('Users');

        $cookieTitle = Configure::read('Session.cookie') . '-AU';
        $this->PersistentCookie = new CurrentUserCookie(
            $this->Cookie,
            $cookieTitle
        );

        $this->configureAuthentication($Controller->Auth);

        // don't auto-login on login related pages
        $excluded = ['login', 'register'];
        if (!in_array($this->_Controller->params['action'], $excluded)) {
            if (!$this->_reLoginSession()) {
                $this->_reLoginCookie();
            }
        }

        if ($this->isLoggedIn()) {
            $this->LastRefresh = new LastRefresh\LastRefreshDatabase($this);
            /* @var UserReadsTable $storage */
            $storage = TableRegistry::get('UserReads');
            $this->ReadEntries = new ReadPostingsDatabase($this, $storage);
        } elseif ($this->isBot()) {
            $this->LastRefresh = new LastRefresh\LastRefreshDummy($this);
            $this->ReadEntries = new ReadPostingsDummy($this);
        } else {
            $this->LastRefresh = new LastRefresh\LastRefreshCookie($this);
            $storage = new Storage($this->Cookie, 'Saito-Read');
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
            $userId = $this->_Controller->request->getSession()->id();
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
        return $this->_Controller->request->is('bot');
    }

    /**
     * Try to login login user from sended request login-form-data.
     *
     * @return bool success
     */
    public function login()
    {
        // non-logged in session-id is lost after successful login
        $sessionId = session_id();

        $user = $this->_Controller->Auth->identify();
        if (!$user || !$this->_login($user)) {
            return false;
        }
        $this->_Controller->Auth->setUser($user);
        $user = $this->_User->get($this->getId());
        $this->_User->incrementLogins($user);
        $this->_User->UserOnline->setOffline($sessionId);

        //= password update
        $password = $this->_Controller->request->getData('password');
        if ($password) {
            $this->_User->autoUpdatePassword($this->getId(), $password);
        }

        //= set persistent Cookie
        $setCookie = (bool)$this->_Controller->request->getData('remember_me');
        if ($setCookie) {
            $this->PersistentCookie->write($this);
        };

        return true;
    }

    /**
     * Try to login the provided users as current user.
     *
     * Use the provided session/cookie/form user to retrieve user-info from
     * the DB. Provided one up-do-date truth for all sessions (user got
     * locked, user-type was changend, …)
     *
     * @param array $user user-data
     * @return bool true if user is logged in false otherwise
     */
    protected function _login($user)
    {
        $this->setSettings($user);
        if ($this->isLoggedIn()) {
            $user = $this->_User->getProfile($this->getId());
            $this->setSettings($user);
        }

        return $this->isLoggedIn();
    }

    /**
     * Login user with session.
     *
     * @return bool success
     */
    protected function _reLoginSession()
    {
        $user = $this->_Controller->Auth->user();

        return $this->_login($user);
    }

    /**
     * Login user with cookie.
     *
     * @return bool success
     */
    protected function _reLoginCookie()
    {
        $user = $this->PersistentCookie->read();
        if (!$user) {
            return false;
        }
        if ($this->_login($user)) {
            $this->_Controller->Auth->setUser($user);
        }

        return $this->isLoggedIn();
    }

    /**
     * Logout user.
     *
     * @return void
     */
    public function logout()
    {
        if (!$this->isLoggedIn()) {
            return;
        }
        $this->PersistentCookie->delete();
        $this->_User->UserOnline->setOffline($this->getId());
        $this->setSettings(null);
        $this->_Controller->Auth->logout();
    }

    /**
     * {@inheritDoc}
     */
    public function beforeRender()
    {
        // write out the current user for access in the views
        $this->_Controller->set('CurrentUser', $this);
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
        $auth->setConfig(
            'authenticate',
            [AuthComponent::ALL => ['finder' => 'allowedToLogin'], 'Mlf', 'Mlf2', 'Form']
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
        $this->_Controller->redirect(['_name' => 'logout']);
    }
}
