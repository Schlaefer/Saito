<?php

	namespace Saito\User;

	trait SaitoUserTrait {

		static private $__accessions = array (
			'anon'	=> 0,
			'user'	=> 1,
			'mod'		=> 2,
			'admin'	=> 3,
		);

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


		/**
		 * User settings
		 *
		 * @var array
		 */
		protected $_settings = null;

		public function setSettings($user) {
			if (empty($user)) {
				$this->_id = null;
				$this->_settings = null;
				$this->_isLoggedIn = false;
				return false;
			}

			if (empty($user) || !is_array($user)) {
				trigger_error("Can't find user.");
			}

			if (empty($user['id']) === false) {
				$this->_id = (int)$user['id'];
				$this->_isLoggedIn = true;
			}

			$this->_settings = $user;

			// perf-cheat
			if (array_key_exists('last_refresh', $this->_settings)) {
				$this->_settings['last_refresh_unix'] = strtotime($this->_settings['last_refresh']);
			}
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

		public function isSame($user) {
			$id = null;
			if (is_int($user)) {
				$id = $user;
			} elseif (is_string($user)) {
				$id = (int)$user;
			} elseif (is_array($user)) {
				if (isset($user['User']['id'])) {
					$id = (int)$user['User']['id'];
				} elseif (isset($user['id'])) {
					$id = (int)$user['id'];
				}
			} elseif ($user instanceof ForumsUserInterface) {
				$id = $user->getId();
			}
			return $id === $this->getId();
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

		/**
		 * checks if current user ignores user with ID $userId
		 *
		 * @param int $userId
		 * @return bool
		 */
		public function ignores($userId) {
			if (!$this->isLoggedIn()) {
				return false;
			}
			return isset($this->_settings['ignores'][$userId]);
		}

		public function isAdmin() {
			return self::_isAdminForRole($this->_settings['user_type']);
		}

		public function isForbidden() {
			if (!empty($this->_settings['user_lock'])) {
				return 'locked';
			}
			if (!empty($this->_settings['activate_code'])) {
				return 'unactivated';
			}
			return false;
		}

		public function mockUserType($type) {
			$MockedUser = clone $this;
			$MockedUser['user_type'] = $type;
			return $MockedUser;
		}

		protected function _getRole() {
			if ($this->_id === null) {
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
			if (isset(self::$__accessions[$userType])) :
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