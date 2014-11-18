<?php

	namespace Saito\User\Cookie;

	use Cake\Controller\Component\CookieComponent;

	/**
	 * Class Storage
	 *
	 * base cookie class for Saito with some default values
	 *
	 * @package Saito\User\Cookie
	 */
	class Storage {

		protected $_Cookie;

		protected $_defaults = [
			'encryption' => 'aes',
			'expires' => '+1 month',
			'httpOnly' => true
		];

		protected $_key;

		public function __construct(CookieComponent $Cookie, $key) {
			$this->_Cookie = $Cookie;
			$this->_key = $key;
            return $this;
		}

		public function read() {
			return $this->_Cookie->read($this->_key);
		}

		public function write($data) {
			$this->_Cookie->write($this->_key, $data);
		}

		public function delete() {
			$this->_Cookie->delete($this->_key);
		}

        public function setConfig($options) {
            $this->_Cookie->configKey($this->_key, $options + $this->_defaults);
            return $this;
        }

	}
