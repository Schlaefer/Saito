<?php

	App::uses('SaitoUserCookieStorage', 'Lib/SaitoUser/Cookies');

	/**
	 * Handles the persistent cookie for cookie relogin
	 */
	class SaitoCurrentUserCookie extends SaitoUserCookieStorage {

		protected $_Cookie;

		public function write($CurrentUser) {
			$cookie = [
				'id' => $CurrentUser->getId(),
				'username' => $CurrentUser['username'],
				'password' => $CurrentUser['password']
			];
			parent::write($cookie);
		}

		/**
		 * Gets cookie values
		 *
		 * @return bool|array cookie values if found, `false` otherwise
		 */
		public function read() {
			$cookie = parent::read();
			if (is_null($cookie) ||
				// cookie couldn't be deciphered correctly and is a meaningless string
				!is_array($cookie)
			) {
				parent::delete();
				return false;
			}
			return $cookie;
		}

	}
