<?php

	App::import('Lib', 'SaitoUser');

	Class CurrentUserComponent extends SaitoUser {

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
		public $components = ['Cookie'];

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
		 * User agent snippets for bots
		 *
		 * @var array
		 */
		protected $_botUserAgents = [
			'archive',
			'bot',
			'baidu',
			'crawl',
			'googlebot',
			'msnbot',
			'spider',
			'slurp',
			'validator'
		];

		/**
		 *
		 * @param type $controller
		 */
		public function initialize(Controller $Controller) {
			if ($Controller->name === 'CakeError') {
				return;
			}
			$this->_Controller = $Controller;

			$this->PersistentCookie = new SaitoCurrentUserCookie($this->_Controller->Cookie);
			$this->PersistentCookie->initialize($this);

			/*
			 * We create a new User Model instance. Otherwise we would overwrite $this->request->data
			 * when reading in refresh(), causing error e.g. saving the user prefs.
			 */
			$this->_User = ClassRegistry::init(
				['class' => 'User', 'alias' => 'currentUser']
			);

			$this->_configureAuth();
			$authSuccess = $this->_Controller->Auth->login();

			// try relogin via cookie
			if ($authSuccess !== true) {
				if (
						$this->_Controller->params['action'] !== 'login' &&
						$this->_Controller->params['action'] !== 'register' &&
						$this->_Controller->referer() !== '/users/login'
				):
					$this->_cookieRelogin();
				endif;
			}

			$this->refresh();
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

			$id = $this->getId();
			if ($this->isLoggedIn() === false):
				// don't count search bots as guests
				if ($this->_isBot()) {
					return;
				}
				$id = session_id();
			endif;
			$this->_User->UserOnline->setOnline($id, $this->isLoggedIn());

			Stopwatch::stop('CurrentUser->_markOnline()');
		}

		/**
		 * Detects if the current user is a bot
		 *
		 * @return boolean
		 */
		protected function _isBot() {
			return preg_match(
				'/' . implode('|', $this->_botUserAgents) . '/i',
				env('HTTP_USER_AGENT')
			) == true;
		}

		protected function _cookieRelogin() {
			$cookie = $this->PersistentCookie->get();
			if ($cookie) {
				// is_array -> if cookie could no be correctly deciphered it's just an random string
				if (!is_null($cookie) && is_array($cookie)):
					if ($this->_Controller->Auth->login($cookie)):
						return;
					endif;
				endif;
				$this->PersistentCookie->destroy();
			}
		}

		public function login() {
			if ($this->_Controller->Auth->login() !== true) {
				return false;
			}

			$this->refresh();
			$this->_User->incrementLogins($this->getId());
			$this->_User->UserOnline->setOffline(session_id());

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

		public function refresh($user = null) {
			parent::set($this->_Controller->Auth->user());
			// all session have to use current user data (locked, user_type, …)
			if ($this->isLoggedIn()) {
				$this->_User->id = $this->getId();
				parent::set($this->_User->getProfile($this->getId()));
				$this->LastRefresh = new SaitoCurrentUserLastRefresh($this, $this->_User);
			}
		}

		public function logout() {
			$this->PersistentCookie->destroy();
			$this->_User->id = $this->getId();
			$this->_User->UserOnline->delete($this->getId(), false);
			$this->set(null);
			$this->_Controller->Session->destroy();
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

		public function getBookmarks() {
			if ($this->isLoggedIn() === false) {
				return [];
			}
			if ($this->_bookmarks === null) {
				$this->_bookmarks = [];
				$bookmarks        = $this->_User->Bookmark->findAllByUserId(
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
					'contain'  => false,
					'scope'    => [
						// user has activated his account (e.g. email confirmation)
						'User.activate_code' => false,
						// user is not banned by admin or mod
						'User.user_lock'     => false
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
	Class SaitoCurrentUserCookie {

		protected $_cookie;
		protected $_cookieName = 'SaitoPersistent';
		protected $_cookiePrefix = 'AU';

		protected $_currentUser;

		public function __construct(CookieComponent $cookie) {
			$this->_cookie = $cookie;
			$this->_setup();
		}

		public function initialize(SaitoUser $currentUser) {
			$this->_currentUser = $currentUser;
			$this->_cookieName  = $currentUser->getPersistentCookieName();
		}

		protected function _setup() {
			$this->_cookie->name = $this->_cookieName;
			$this->_cookie->type('rijndael');
		}

		public function set() {
			$cookie              = [
				'id'       => $this->_currentUser->getId(),
				'username' => $this->_currentUser['username'],
				'password' => $this->_currentUser['password']
			];
			$this->_cookie->name = $this->_cookieName;
			$this->_cookie->write($this->_cookiePrefix, $cookie, true, '+4 weeks');
		}

		public function destroy() {
			$this->_cookie->destroy();
		}

		public function get() {
			return $this->_cookie->read($this->_cookiePrefix);
		}
	}

	/**
	 * Mark-Entries-As-Read-User-Last-Refresh-Time-O-Mat
	 */
	Class SaitoCurrentUserLastRefresh {

		protected $user;
		protected $currentUser;

		public function __construct(SaitoUser $currentUser, User $user) {
			$this->currentUser = $currentUser;
			$this->user        = $user;
		}

		/**
		 * @param mixed $timestamp
		 *
		 * null|'now'|<`Y-m-d H:i:s` timestamp>
		 */
		public function set($timestamp = null) {
			if ($timestamp === 'now') {
				$this->_set(date("Y-m-d H:i:s"));
			} elseif ($timestamp === null) {
				$timestamp = $this->currentUser['last_refresh_tmp'];
			}
			$this->_set($timestamp);
		}

		public function setMarker() {
			$this->user->setLastRefresh();
		}

		protected function _set($newLastRefresh) {
			$this->user->setLastRefresh($newLastRefresh);
			$this->currentUser['last_refresh'] = $newLastRefresh;
		}

	}
