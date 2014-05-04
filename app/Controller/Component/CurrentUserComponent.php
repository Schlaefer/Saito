<?php

	App::import('Lib/SaitoUser', 'SaitoUser');
	App::uses('SaitoCurrentUserReadEntries', 'Lib/SaitoUser');

	class CurrentUserComponent extends SaitoUser {

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
 * Name for the persistent Cookie
 *
 * @var string
 */
		protected $_persistentCookieName = 'SaitoPersistent';

/**
 * Manages the last refresh/mark entries as read for the current user
 *
 * @var SaitoLastRefresh
 */
		public $LastRefresh = null;

		public $ReadEntries;

/**
 * Model User instance exclusive to the CurrentUserComponent
 *
 * @var User
 */
		protected $_User = null;

/**
 * array with ids of all user's bookmarks
 *
 * For performance we cache User->Bookmark->find() here.
 *
 * format: [entry_id => id, …]
 *
 * @var array
 */
		protected $_bookmarks = null;

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

			/*
			 * We create a new User Model instance. Otherwise we would overwrite $this->request->data
			 * when reading in refresh(), causing error e.g. saving the user prefs.
			 */
			$this->_User = ClassRegistry::init(
					['class' => 'User', 'alias' => 'currentUser']
			);

			$this->PersistentCookie = new SaitoCurrentUserCookie($this->_Controller->Cookie);
			$this->PersistentCookie->initialize($this);
			$this->ReadEntries = new SaitoCurrentUserReadEntries($this);

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
				$this->Cron->addCronJob('ReadUser.' . $this->getId(),
						'hourly',
						[$this->ReadEntries, 'gcUser']);
			}
			$this->Cron->addCronJob('ReadUser.global',
					'hourly',
					[$this->ReadEntries, 'gcGlobal']);

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
			$cookie = $this->PersistentCookie->get();
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
				$this->PersistentCookie->set();
			};

			return true;
		}

		/**
		 * Sets user-data
		 */
		public function refresh() {
			// preliminary set user-data from Cake's Auth handler
			parent::set($this->_Controller->Auth->user());
			// set user-data from current DB data: ensures that *all sessions*
			// use the same set of data (user got locked, user-type was demoted …)
			if ($this->isLoggedIn()) {
				$this->_User->id = $this->getId();
				parent::set($this->_User->getProfile($this->getId()));
				$this->LastRefresh = new SaitoCurrentUserLastRefresh($this, $this->_User);
			}
		}

		public function logout() {
			if (!$this->isLoggedIn()) {
				return;
			}
			$this->PersistentCookie->destroy();
			$this->_User->id = $this->getId();
			$this->_User->UserOnline->setOffline($this->getId());
			$this->set(null);
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

/**
 * Returns the name of the persistent cookie
 *
 * @return string
 */
		public function getPersistentCookieName() {
			return $this->_persistentCookieName;
		}

		public function getModel() {
			return $this->_User;
		}

		public function getBookmarks() {
			if ($this->isLoggedIn() === false) {
				return [];
			}
			if ($this->_bookmarks === null) {
				$this->_bookmarks = [];
				$bookmarks = $this->_User->Bookmark->findAllByUserId(
					$this->getId(),
					['contain' => false]
				);
				if (!empty($bookmarks)) {
					foreach ($bookmarks as $bookmark) {
						$this->_bookmarks[(int)$bookmark['Bookmark']['entry_id']] = (int)$bookmark['Bookmark']['id'];
					}
				}
			}
			return $this->_bookmarks;
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

/**
 * Handles the persistent cookie for cookie relogin
 */
	class SaitoCurrentUserCookie {

		protected $_cookie;

		protected $_cookieName = 'SaitoPersistent';

		protected $_cookiePrefix = 'AU';

		protected $_currentUser;

		public function __construct(CookieComponent $cookie) {
			$this->_cookie = $cookie;
			$this->_setup();
		}

		protected function _setup() {
			$this->_cookie->name = $this->_cookieName;
			$this->_cookie->type('rijndael');
			$this->_cookie->httpOnly = true;
		}

		public function initialize(SaitoUser $currentUser) {
			$this->_currentUser = $currentUser;
			$this->_cookieName = $currentUser->getPersistentCookieName();
		}

		public function set() {
			$cookie = [
				'id' => $this->_currentUser->getId(),
				'username' => $this->_currentUser['username'],
				'password' => $this->_currentUser['password']
			];
			$this->_cookie->name = $this->_cookieName;
			$this->_cookie->write($this->_cookiePrefix, $cookie, true, '+4 weeks');
		}

		public function destroy() {
			$this->_cookie->destroy();
		}

		/**
		 * Gets cookie values
		 *
		 * @return bool|array cookie values if found, `false` otherwise
		 */
		public function get() {
			$cookie = $this->_cookie->read($this->_cookiePrefix);
			if (is_null($cookie) ||
					// cookie couldn't be deciphered correctly and is a meaningless string
					!is_array($cookie)
			) {
				$this->destroy();
				return false;
			}
			return $cookie;
		}

	}

/**
 * Mark-Entries-As-Read-User-Last-Refresh-Time-O-Mat
 */
	class SaitoCurrentUserLastRefresh {

		protected $_user;

		protected $_currentUser;

		public function __construct(SaitoUser $currentUser, User $user) {
			$this->_currentUser = $currentUser;
			$this->_user = $user;
		}

/**
 * @param mixed $timestamp
 *
 * null|'now'|<`Y-m-d H:i:s` timestamp>
 */
		public function set($timestamp = null) {
			if ($timestamp === 'now') {
				$timestamp = date('Y-m-d H:i:s');
			} elseif ($timestamp === null) {
				$timestamp = $this->_currentUser['last_refresh_tmp'];
			}

			$this->_user->setLastRefresh($timestamp);
			$this->_currentUser['last_refresh'] = $timestamp;
			$this->_currentUser->ReadEntries->delete();
		}

		public function setMarker() {
			$this->_user->setLastRefresh();
		}

	}
