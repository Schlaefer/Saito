<?php

	App::uses('SaitoUserUserlistInterface', 'Lib/SaitoUser/Userlist');

	class SaitoUserUserlistArray implements SaitoUserUserlistInterface {

		protected $_userlist = [];

		public function set($userlist) {
			$this->_userlist = $userlist;
		}

		public function get() {
			return $this->_userlist;
		}

	}
