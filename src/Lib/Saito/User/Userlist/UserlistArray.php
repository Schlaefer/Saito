<?php

	namespace Saito\User\Userlist;

	class UserlistArray implements UserlistInterface {

		protected $_userlist = [];

		public function set($userlist) {
			$this->_userlist = $userlist;
		}

		public function get() {
			return $this->_userlist;
		}

	}
