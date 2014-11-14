<?php

	namespace Saito\User\Cookie;

	class Storage {

		const LIFETIME = '31 Days';

		const NAME = 'SaitoUser';

		public $encrypt = true;

		protected $_Cookie;

		protected $_key;

		public function __construct(\CookieComponent $Cookie, $key) {
			$this->_Cookie = $Cookie;
			$this->_key = $key;
			$this->_setup();
		}

		public function read() {
			return $this->_Cookie->read($this->_key);
		}

		public function write($data) {
			$this->_Cookie->write($this->_key, $data, $this->encrypt, self::LIFETIME);
		}

		public function delete() {
			$this->_Cookie->delete($this->_key);
		}

		protected function _setup() {
			$this->_Cookie->type('aes');
			$this->_Cookie->httpOnly = true;
			$this->_Cookie->name = self::NAME;
		}

	}