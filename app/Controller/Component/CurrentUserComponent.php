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
	public $components 	= array( 'Cookie' );

	/**
	 * Manages the persistent login cookie
	 * 
	 * @var SaitoCurrentUserCookie
	 */
	public	$PersistentCookie = NULL;

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
	public $LastRefresh	= NULL;

	/**
	 * Model User instance exclusive to the CurrentUserComponent
	 *
	 * @var User
	 */
	protected $_User = NULL;

	/**
	 * Reference to the controller
	 *
	 * @var Controller
	 */
	protected $_Controller = NULL;

	/**
	 *
	 * @param type $controller
	 */
	public function initialize($controller) {
		if ($controller->name === 'CakeError') {
			return;
		}
		$this->_Controller = $controller;

		$this->PersistentCookie = new SaitoCurrentUserCookie($this->_Controller->Cookie);

		/*
		 * We create a new User Model instance. Otherwise we would overwrite $this->request->data
		 * when reading in refresh(), causing error e.g. saving the user prefs.
		 */
		$this->_User = ClassRegistry::init(array( 'class' => 'User', 'alias' => 'currentUser' ));

		$this->refresh();
		$this->_configureAuth();

		//* Try Cookie Relogin
		if (!$controller->Auth->login()) {
			// for performance reasons Security::cypher() we check the cookie first
			// not using the framework
			if (	 isset($_COOKIE[$this->_persistentCookieName])
					&& $controller->params['action'] != 'login'
					&& $controller->params['action'] != 'register'
					&& $controller->referer() != '/users/login'
			):
				$this->_cookieRelogin();
			endif;
		}

		$this->_markOnline();
	}

  public function startup(Controller $controller) {
    parent::startup($controller);

    if ( $controller->action !== 'logout' && $this->isLoggedIn() ) :
      if ( $this->isForbidden() ) :
        $this->_Controller->redirect(array('controller' => 'users', 'action'=>'logout'));
      endif;
    endif;

  }

	/**
	 * Marks users as online
	 *
	 */
	protected function _markOnline() {
		Stopwatch::start('CurrentUser->_markOnline()');

		$id = $this->getId();
		if ( $this->isLoggedIn() == FALSE ):
			$id = session_id();
		endif;
		$this->_User->UserOnline->setOnline($id, $this->isLoggedIn());

		Stopwatch::stop('CurrentUser->_markOnline()');
	}

	protected function _cookieRelogin() {
		$cookie = $this->PersistentCookie->get();
    // is_array -> if cookie could no be correctly deciphered it's just an random string
		if ( !is_null($cookie) && is_array($cookie) ):
			if ( $this->_Controller->Auth->login($cookie) ):
				$this->refresh();
				return;
		  endif;
		endif;
		$this->PersistentCookie->destroy();
	}

	public function refresh() {
		parent::set($this->_Controller->Session->read('Auth.User'));

		$this->PersistentCookie->initialize($this);

		//*  make shure all session have the same userdata
		if ( $this->isLoggedIn() ) {
			$this->_User->id = $this->getId();
			$user = $this->_User->getProfile($this->getId());
			parent::set($user['currentUser']);
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

	public function shutdown($controller) {
		$this->_writeSession($controller);
	}

// end shutdown();

	public function beforeRedirect(Controller $Controller) {
		$this->_writeSession($Controller);

	}

// end beforeRedirect()

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

	/**
	 * write the settings to the session, so that they are available on next request
	 */
	protected function _writeSession(&$controller) {
		if ( $controller->action !== 'logout' && $controller->Auth->user() ) :
			$controller->Session->write('Auth.User',
					$this->getSettings());
		endif;
	}

	/**
	 * Configures the auth component
	 */
	protected function _configureAuth() {
		// delegate authenticate method
		// $this->_Controller->Auth->authenticate = $this->_User;

    $authCommonSetup = array(
          'useModel' => 'User',
          'contain'  => false,
          'scope' => array(
              // user has activated his account (e.g. email confirmation)
              'User.activate_code' => false,
              // user is not banned by admin or mod
              'User.user_lock' => false,
          )
      );
		$this->_Controller->Auth->authenticate = array(
          // blowfish saito standard
          'BcryptAuthenticate.Bcrypt' => $authCommonSetup,
          // mylittleforum 1 authentication
          'Mlf' => $authCommonSetup,
          // mylittleforum 2 auth
          'Mlf2' => $authCommonSetup,
      );

		if ( $this->isLoggedIn() ):
			$this->_Controller->Auth->allow();
		else:
			$this->_Controller->Auth->deny();
		endif;

		// we have some work todo in users_c/login() before redirecting
		$this->_Controller->Auth->autoRedirect = false;

		// access to static pages in views/pages is allowed
		$this->_Controller->Auth->allow('display');

		// l10n
		$this->_Controller->Auth->authError = __('auth_autherror');
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

	public function initialize(SaitoUser $currentUser ) {
		$this->_currentUser = $currentUser;
		$this->_cookieName  = $currentUser->getPersistentCookieName();
	}

	protected function _setup() {
		$this->_cookie->name	= $this->_cookieName;
    $this->_cookie->type('rijndael');
	}

	public function set() {
			$cookie = array();
			$cookie['id'] 				= $this->_currentUser->getId();
			$cookie['username'] 	= $this->_currentUser['username'];
			$cookie['password'] 	= $this->_currentUser['password'];
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
		$this->user = $user;
	}

	public function forceSet() {
		$this->_set(date("Y-m-d H:i:s"));
	}

	public function set() {
		$this->_set($this->currentUser['last_refresh_tmp']);
	}

	public function setMarker() {
		$this->user->setLastRefresh();
	}

	protected function _set($newLastRefresh) {
		$this->user->setLastRefresh($newLastRefresh);
		$this->currentUser['last_refresh'] = $newLastRefresh;
	}

}

?>