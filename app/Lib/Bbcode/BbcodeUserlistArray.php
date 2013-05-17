<?php

	App::uses('BbcodeUserlistInterface', 'Lib/Bbcode');

	class BbcodeUserlistArray implements BbcodeUserlistInterface {
		protected $_userlist = [];

		public function set($userlist) {
			$this->_userlist = $userlist;
		}

		public function get() {
			return $this->_userlist;
		}
	}
