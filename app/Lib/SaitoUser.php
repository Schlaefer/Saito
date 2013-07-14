<?php

interface ForumsUser {

	public function set($user);

	public function getSettings();

	public function getId();

	public function isUser();

	public function isMod();

	public function isModOnly();

	public function isAdmin();

	public function isLoggedIn();

	public function isForbidden();

	public function getMaxAccession();

	public function mockUserType($type);
}

class SaitoUser extends Component implements ForumsUser, ArrayAccess {

	static private $__accessions = array (
			'anon'	=> 0,
			'user'	=> 1,
			'mod'		=> 2,
			'admin'	=> 3,
	);

	/**
	 * User model
	 *
	 * @var object
	 */
	protected $_Instance = NULL;
	/**
	 * User settings
	 *
	 * @var array
	 */
	protected $_settings = NULL;
	/**
	 * User ID
	 *
	 * @var int
	 */
	protected $_id = null;
	/**
	 * Stores if a user is logged in
	 *
	 * @var bool
	 */
	protected $_isLoggedIn = false;

	public function set($user) {
		if ( empty($user) ) {
			$this->_id = NULL;
			$this->_settings = NULL;
			$this->_isLoggedIn = FALSE;
			return false;
		}

		if ( !empty($user) && is_array($user) ) :
			if (empty($user['id']) === false) {
				$this->_id = $user['id'];
				$this->_isLoggedIn = true;
			}
			$this->_settings = $user;
		else :
			trigger_error("Can't find user.");
		endif;

	}

	public function getSettings() {
		return $this->_settings;

	}

	public function getId() {
		return $this->_id;

	}

	public function isLoggedIn() {
		return $this->_isLoggedIn;

	}

	public function isUser() {
		return self::_isUserForRole($this->_settings['user_type']);

	}

	public function isMod() {
		return self::_isModForRole($this->_settings['user_type']);

	}

	public function isModOnly() {
		return self::$__accessions[$this->_getRole()] === 2;
	}

	public function isAdmin() {
		return self::_isAdminForRole($this->_settings['user_type']);

	}

	public function isForbidden() {
		return empty($this->_settings['user_lock']) === FALSE;
	}

	public function mockUserType($type) {
		$MockedUser = clone $this;
		$MockedUser['user_type'] = $type;
		return $MockedUser;
	}

	protected function _getRole() {
		if ( $this->_id === null ) {
			return 'anon';
		} else {
			return $this->_settings['user_type'];
		}
	}

	protected static function _isUserForRole($userType) {
		$accession = self::_maxAccessionForUserType($userType);
		return ($accession >= 1) ? true : false;

	}

	protected static function _isModForRole($userType) {
		$accession = self::_maxAccessionForUserType($userType);
		return ($accession >= 2) ? true : false;

	}

	protected static function _isAdminForRole($userType) {
		$accession = self::_maxAccessionForUserType($userType);
		return ($accession === 3) ? true : false;

	}

	/**
	 * Get maximum value of the allowed accession
	 *
	 * Very handy for DB requests
	 *
	 * @mlf some day we will have user->type->accession->categories tables and relations,
	 * that will be an happy day
	 *
	 * @return int
	 */
	public function getMaxAccession() {
		$userType = $this->_getRole();
		return self::_maxAccessionForUserType($userType);

	}

	protected static function _maxAccessionForUserType($userType) {
		if ( isset(self::$__accessions[$userType]) ) :
			return self::$__accessions[$userType];
		else :
			return 0;
		endif;
	}

	public function offsetExists($offset) {
		return isset($this->_settings[$offset]);
	}

	public function offsetGet($offset) {
		return $this->_settings[$offset];
	}

	public function offsetSet($offset, $value) {
		$this->_settings[$offset] = $value;
	}

	public function offsetUnset($offset) {
		unset($this->_settings[$offset]);
	}

}

?>