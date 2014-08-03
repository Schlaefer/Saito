<?php

	App::uses('LastRefreshAbstract', 'Lib/SaitoUser/LastRefresh');
	App::uses('SaitoUserCookieStorage', 'Lib/SaitoUser/Cookies');

	/**
	 * handles last refresh time for current user via cookie
	 *
	 * used for non logged-in users
	 */
	class LastRefreshCookie extends LastRefreshAbstract {

		protected $_Cookie;

		public function __construct(CurrentuserComponent $CurrentUser) {
			$this->_CurrentUser = $CurrentUser;
			$this->_Cookie = new SaitoUserCookieStorage(
				$this->_CurrentUser->Cookie,
				'lastRefresh'
			);
		}

		protected function _get() {
			if ($this->_timestamp === null) {
				$this->_timestamp = $this->_Cookie->read();
				if (empty($this->_timestamp)) {
					$this->_timestamp = false;
				} else {
					$this->_timestamp = strtotime($this->_timestamp);
				}
			}
			return $this->_timestamp;
		}

		protected function _set() {
			$this->_Cookie->write($this->_timestamp);
		}

	}
