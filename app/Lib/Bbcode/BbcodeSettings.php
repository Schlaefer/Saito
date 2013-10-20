<?php

	/**
	 * Class BbcodeSettings
	 *
	 * Singleton to share global settings between bbcode modules
	 */
	class BbcodeSettings implements ArrayAccess {

		protected $_settings = [];

		private static $__instance = null;

		public static function getInstance() {
			if (self::$__instance === null) {
				self::$__instance = new BbcodeSettings();
			}
			return self::$__instance;
		}

		public function set($settings) {
			$this->_settings = $settings + $this->_settings;
		}

		public function get() {
			return $this->_settings;
		}

		protected function __construct() {
		}

		private function __clone() {
		}

		public function offsetExists($offset) {
			isset(self::$__instance->_settings[$offset]);
		}

		public function offsetGet($offset) {
			return self::$__instance->_settings[$offset];
		}

		public function offsetSet($offset, $value) {
			self::$__instance->_settings[$offset] = $value;
		}

		public function offsetUnset($offset) {
			unset(self::$__instance->_settings[$offset]);
		}

	}