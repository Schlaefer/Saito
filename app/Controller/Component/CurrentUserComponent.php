<?php

	App::uses('Component', 'Controller');

	App::uses('ReadPostingsDatabase', 'Lib/SaitoUser/ReadPostings');
	App::uses('ReadPostingsCookie', 'Lib/SaitoUser/ReadPostings');
	App::uses('ReadPostingsDummy', 'Lib/SaitoUser/ReadPostings');
	App::uses('LastRefreshCookie', 'Lib/SaitoUser/LastRefresh');
	App::uses('LastRefreshDatabase', 'Lib/SaitoUser/LastRefresh');
	App::uses('LastRefreshDummy', 'Lib/SaitoUser/LastRefresh');
	App::uses('UserBookmarks', 'Lib');

	App::uses('SaitoCurrentUserCookie', 'Lib/SaitoUser/Cookies');
	App::uses('SaitoUserTrait', 'Lib/SaitoUser');
	App::uses('ForumsUserInterface', 'Lib/SaitoUser');
	App::uses('CategoryAuth', 'Lib/SaitoUser');

	class CurrentUserComponent extends Component implements ForumsUserInterface, ArrayAccess {

		use SaitoUserTrait;

		/**
		 * @var CategoryAuth
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
 * @var SaitoCurrentUserCookie
 */
		public $PersistentCookie = null;

/**
 * Manages the last refresh/mark entries as read for the current user
 *
 * @var LastRefreshAbstract
 */
		public $LastRefresh = null;

	/**
	 * @var ReadPostings
	 */
		public $ReadEntries;

		/**
		 * @var UserBookmarks bookmarks of the current user
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

/**
 *
 * @param type $controller
 */
		public function initialize(Controller $Controller) {
			if ($Controller->name === 'CakeError') {
				return;
			}
			$this->_Controller = $Controller;
			if ($this->_Controller->modelClass) {
				$this->_Controller->{$this->_Controller->modelClass}->SharedObjects['CurrentUser'] = $this;
			}
			$this->Categories = new CategoryAuth($this);

			/*
			 * We create a new User Model instance. Otherwise we would overwrite $this->request->data
			 * when reading in refresh(), causing error e.g. saving the user prefs.
			 */
			$this->_User = ClassRegistry::init(
					['class' => 'User', 'alias' => 'currentUser']
			);

			$this->PersistentCookie = new SaitoCurrentUserCookie($this->Cookie, 'AU');

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
				$this->ReadEntries = new ReadPostingsDatabase($this);
			} elseif ($this->isBot()) {
				$this->ReadEntries = new ReadPostingsDummy($this);
			} else {
				$this->ReadEntries = new ReadPostingsCookie($this);
			}

			$this->_Bookmarks = new UserBookmarks($this);

			$this->_markOnline();
		}

		public function startup(Controller $controller) {
			parent::startup($controller);

			if ($controller->action !== 'logout' && $this->isLoggedIn()) :
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
		protected function _markOnline() {
			Stopwatch::start('CurrentUser->_markOnline()');
			$_isLoggedIn = $this->isLoggedIn();
			if ($_isLoggedIn) {
				$_id = $this->getId();
			} else {
				// don't count search bots as guests
				if ($this->isBot()) {
					return;
				}
				$_id = $this->_Controller->Session->id();
			}
			$this->_User->UserOnline->setOnline($_id, $_isLoggedIn);
			Stopwatch::stop('CurrentUser->_markOnline()');
		}

		/**
		 * Detects if the current user is a bot
		 *
		 * @return boolean
		 */
		public function isBot() {
			return $this->_Controller->request->is('bot');
		}

		/**
		 * Logs-in registered users
		 *
		 * @param null|array $user user-data, if null request-data is used
		 * @return bool true if user is logged in false otherwise
		 */
		protected function _login($user = null) {
			$this->_Controller->Auth->login($user);
			$this->refresh();
			return $this->isLoggedIn();
		}

		protected function _reLoginSession() {
			return $this->_login();
		}

		protected function _reLoginCookie() {
			$cookie = $this->PersistentCookie->read();
			if ($cookie) {
				$this->_login($cookie);
				return $this->isLoggedIn();
			}
			return false;
		}

		public function login() {
			// non-logged in session-id is lost after successful login
			$sessionId = session_id();

			if (!$this->_login()) {
				return false;
			}

			$this->_User->incrementLogins($this->getId());
			$this->_User->UserOnline->setOffline($sessionId);
			//password update
			if (empty($this->_Controller->request->data['User']['password']) === false) {
				$this->_User->autoUpdatePassword(
					$this->getId(),
					$this->_Controller->request->data['User']['password']
				);
			}

			// set cookie
			if (empty($this->_Controller->request->data['User']['remember_me']) === false) {
				$this->PersistentCookie->write($this);
			};

			return true;
		}

		/**
		 * Sets user-data
		 */
		public function refresh() {
			// preliminary set user-data from Cake's Auth handler
			$this->setSettings($this->_Controller->Auth->user());
			// set user-data from current DB data: ensures that *all sessions*
			// use the same set of data (user got locked, user-type was demoted …)
			if ($this->isLoggedIn()) {
				$this->_User->id = $this->getId();
				$this->setSettings($this->_User->getProfile($this->getId()));
				$this->LastRefresh = new LastRefreshDatabase($this);
			} elseif ($this->isBot()) {
				$this->LastRefresh = new LastRefreshDummy($this);
			} else {
				$this->LastRefresh = new LastRefreshCookie($this);
			}
		}

		public function logout() {
			if (!$this->isLoggedIn()) {
				return;
			}
			$this->PersistentCookie->delete();
			$this->_User->id = $this->getId();
			$this->_User->UserOnline->setOffline($this->getId());
			$this->setSettings(null);
			$this->_Controller->Auth->logout();
		}

		public function shutdown(Controller $Controller) {
			$this->_writeSession($Controller);
		}

		public function beforeRedirect(Controller $Controller, $url, $status = null, $exit = true) {
			$this->_writeSession($Controller);
		}

		public function beforeRender(Controller $Controller) {
			// write out the current user for access in the views
			$Controller->set('CurrentUser', $this);
		}

		public function getModel() {
			return $this->_User;
		}

		public function hasBookmarked($entryId) {
			return $this->_Bookmarks->isBookmarked($entryId);
		}

/**
 * write the settings to the session, so that they are available on next request
 */
		protected function _writeSession(&$controller) {
			if ($controller->action !== 'logout' && $controller->Auth->user()):
				$controller->Session->write(
					'Auth.User',
					$this->getSettings()
				);
			endif;
		}

/**
 * Configures the auth component
 */
		protected function _configureAuth() {
			// delegate authenticate method
			// $this->_Controller->Auth->authenticate = $this->_User;

			$this->_Controller->Auth->authenticate = [
				AuthComponent::ALL => [
					'useModel' => 'User',
					'contain' => false,
					'scope' => [
						// user has activated his account (e.g. email confirmation)
						'User.activate_code' => 0,
						// user is not banned by admin or mod
						'User.user_lock' => 0
					]
				],
				// 'Mlf' and 'Mlf2' could be 'Form' with different passwordHasher, but
				// see: https://cakephp.lighthouseapp.com/projects/42648/tickets/3907-allow-multiple-passwordhasher-with-same-authenticate-class-in-auth-config#ticket-3907-1
				'Mlf', // mylittleforum 1 auth
				'Mlf2', // mylittleforum 2 auth
				'Form' => ['passwordHasher' => 'Blowfish'] // blowfish saito standard
			];

			if ($this->isLoggedIn()):
				$this->_Controller->Auth->allow();
			else:
				$this->_Controller->Auth->deny();
			endif;

			$this->_Controller->Auth->autoRedirect = false; // don't redirect after Auth->login()
			$this->_Controller->Auth->allow('display'); // access to static pages in views/pages is allowed
			$this->_Controller->Auth->authError = __('auth_autherror'); // l10n
		}

	}

