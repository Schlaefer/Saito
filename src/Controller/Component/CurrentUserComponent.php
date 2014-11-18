<?php

namespace App\Controller\Component;

use Cake\Controller\Component\AuthComponent;
use Cake\Controller\Component;
use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\ORM\TableRegistry;
use Saito\User\Categories;
use Saito\User\Cookie\Storage;
use Saito\User\ReadPostings\ReadPostingsCookie;
use Saito\User\ReadPostings\ReadPostingsDatabase;
use Saito\User\ReadPostings\ReadPostingsDummy;
use \Stopwatch\Lib\Stopwatch;
use Saito\App\Registry;
use Saito\User\Bookmarks;
use Saito\User\Cookie\CurrentUserCookie;
use Saito\User\ForumsUserInterface;
use Saito\User\LastRefresh;
use Saito\User\ReadPostings;
use Saito\User\SaitoUserTrait;

class CurrentUserComponent extends Component implements
    \ArrayAccess,
    ForumsUserInterface
{

    use SaitoUserTrait;

    /**
     * @var \Saito\User\Category
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
     * @var ReadPostings
     */
    public $ReadEntries;

    /**
     * @var Bookmarks bookmarks of the current user
     */
    protected $_Bookmarks;

    /**
     * Model User instance exclusive to the CurrentUserComponent
     *
     * @var User
     */
    protected $_User = null;

    /**
     * Reference to the controller
     *
     * @var Controller
     */
    protected $_Controller = null;

    public function initialize(array $config)
    {
        $Controller = $this->_registry->getController();
        $this->_Controller = $Controller;
        Registry::set('CU', $this);

        if ($Controller->name === 'CakeError') {
            return;
        }

        $this->Categories = new Categories($this);

        /*
         * We create a new User Model instance. Otherwise we would overwrite $this->request->data
         * when reading in refresh(), causing error e.g. saving the user prefs.
         *
         * was CakePHP 2:
        $this->_User = $ClassRegistry::init(
                ['class' => 'User', 'alias' => 'currentUser']
        );
         */
        $this->_User = TableRegistry::get('Users');

        $cookieTitle = Configure::read('Session.cookie') . '-AU';
        $this->PersistentCookie = new CurrentUserCookie(
            $this->Cookie,
            $cookieTitle
        );

        $this->_configureAuth();

        // prevents session auto re-login from form's request->data: login is
        // called explicitly by controller on /users/login
        if ($this->_Controller->action !== 'login') {
            if (!$this->_reLoginSession()) {
                // don't auto-login on login related pages
                if ($this->_Controller->params['action'] !== 'login' &&
                    $this->_Controller->params['action'] !== 'register' &&
                    $this->_Controller->referer() !== '/users/login'
                ) {
                    $this->_reLoginCookie();
                }
            }
        }

        if ($this->isLoggedIn()) {
            $storage = TableRegistry::get('UserReads');
            $this->ReadEntries = new ReadPostingsDatabase($this, $storage);
        } elseif ($this->isBot()) {
            $this->ReadEntries = new ReadPostingsDummy($this);
        } else {
            $storage = new Storage($this->Cookie, 'Saito-Read');
            $this->ReadEntries = new ReadPostingsCookie($this, $storage);
        }

        // @todo 3.0 bookmarks
//			$this->_Bookmarks = new Bookmarks($this);

        $this->_markOnline();
    }

    public function startup(Event $event)
    {
        if ($this->_Controller->action !== 'logout' && $this->isLoggedIn()) :
            if ($this->isForbidden()) :
                $this->_Controller->redirect(
                    ['controller' => 'users', 'action' => 'logout']
                );
            endif;
        endif;
    }

    /**
     * Marks users as online
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
            $userId = $this->_Controller->request->session()->id();
        }
        $this->_User->UserOnline->setOnline($userId, $isLoggedIn);
        Stopwatch::stop('CurrentUser->_markOnline()');
    }

    /**
     * Detects if the current user is a bot
     *
     * @return boolean
     */
    public function isBot()
    {
        return $this->_Controller->request->is('bot');
    }

    /**
     * Logs-in registered users
     *
     * @param null|array $user user-data, if null request-data is used
     * @return bool true if user is logged in false otherwise
     */
    protected function _login($user = null)
    {
        if ($user === null) {
            $user = $this->_Controller->Auth->identify($user);
        }
        if ($user) {
            // @todo 3.0 do an actual validation if $user is method argument
            $this->_Controller->Auth->setUser($user);
        }
        $this->refresh();

        return $this->isLoggedIn();
    }

    protected function _reLoginSession()
    {
        return $this->_login();
    }

    protected function _reLoginCookie()
    {
        $cookie = $this->PersistentCookie->read();
        if ($cookie) {
            $this->_login($cookie);

            return $this->isLoggedIn();
        }

        return false;
    }

    public function login()
    {
        // non-logged in session-id is lost after successful login
        $sessionId = session_id();

        if (!$this->_login()) {
            return false;
        }

        $user = $this->_User->get($this->getId());
        $this->_User->incrementLogins($user);
        $this->_User->UserOnline->setOffline($sessionId);

        //= password update
        $password = $this->_Controller->request->data('password');
        if ($password) {
            $this->_User->autoUpdatePassword($this->getId(), $password);
        }

        //= set persistent Cookie
        $setCookie = (bool)$this->_Controller->request->data('remember_me');
        if ($setCookie) {
            $this->PersistentCookie->write($this);
        };

        return true;
    }

    /**
     * Sets user-data
     */
    public function refresh()
    {
        // preliminary set user-data from Cake's Auth handler
        $this->setSettings($this->_Controller->Auth->user());
        // set user-data from current DB data: ensures that *all sessions*
        // use the same set of data (user got locked, user-type was demoted …)
        if ($this->isLoggedIn()) {
            $this->setSettings($this->_User->get($this->getId()));
            $this->LastRefresh = new LastRefresh\LastRefreshDatabase($this);
        } elseif ($this->isBot()) {
            $this->LastRefresh = new LastRefresh\LastRefreshDummy($this);
        } else {
            $this->LastRefresh = new LastRefresh\LastRefreshCookie($this);
        }
    }

    public function logout()
    {
        if (!$this->isLoggedIn()) {
            return;
        }
        $this->PersistentCookie->delete();
        $this->_User->id = $this->getId();
        $this->_User->UserOnline->setOffline($this->getId());
        $this->setSettings(null);
        $this->_Controller->Auth->logout();
    }

    public function shutdown(Event $event)
    {
        $this->_writeSession($event->subject());
    }

    public function beforeRedirect(Event $event)
    {
        $this->_writeSession();
    }

    public function beforeRender(Event $event)
    {
        // write out the current user for access in the views
        $this->_Controller->set('CurrentUser', $this);
    }

    /**
     * @return UsersTable
     */
    public function getModel()
    {
        return $this->_User;
    }

    public function hasBookmarked($entryId)
    {
        // @todo 3.0
        return false;

        return $this->_Bookmarks->isBookmarked($entryId);
    }

    /**
     * write the settings to the session, so that they are available on next
     * request
     */
    protected function _writeSession()
    {
        $controller = $this->_Controller;
        // @todo 3.0 bogus
        if ($controller->action !== 'logout' && $controller->Auth->user()):
            $controller->request->session()->write(
                'Auth.User',
                $this->getSettings()
            );
        endif;
    }

    /**
     * Configures the auth component
     */
    protected function _configureAuth()
    {
        $this->_Controller->Auth->config(
            'authenticate',
            [
                AuthComponent::ALL => [
                    'useModel' => 'Users',
                    'contain' => false,
                    'scope' => [
                        // user has activated his account (e.g. email confirmation)
                        'Users.activate_code' => 0,
                        // user is not banned by admin or mod
                        'Users.user_lock' => 0
                    ]
                ],
                // 'Mlf' and 'Mlf2' could be 'Form' with different passwordHasher, but
                // see: https://cakephp.lighthouseapp.com/projects/42648/tickets/3907-allow-multiple-passwordhasher-with-same-authenticate-class-in-auth-config#ticket-3907-1
                'Mlf',
                // mylittleforum 1 auth
                'Mlf2',
                // mylittleforum 2 auth
                'Form' => ['passwordHasher' => 'Default']
                // blowfish saito standard
            ]
        );
        $this->_Controller->Auth->config('authorize', ['Controller']);
        $this->_Controller->Auth->config('loginAction', '/login');
        $this->_Controller->Auth->config('unauthorizedRedirect', '/login');

        if ($this->isLoggedIn()):
            $this->_Controller->Auth->allow();
        else:
            $this->_Controller->Auth->deny();
        endif;

        $this->_Controller->Auth->autoRedirect = false; // don't redirect after Auth->login()
        $this->_Controller->Auth->allow(
            'display'
        ); // access to static pages in views/pages is allowed
        $this->_Controller->Auth->authError = __('auth_autherror'); // l10n
    }

}

