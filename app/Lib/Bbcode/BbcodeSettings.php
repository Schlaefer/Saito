<?php

	/**
	 * Class BbcodeSettings
	 *
	 * Singleton to share global settings between bbcode modules
	 */
	class BbcodeSettings implements ArrayAccess {

		protected $settings = [];

		private static $instance = null;

		static function getInstance() {
			if (self::$instance === null) {
				self::$instance = new BbcodeSettings();
			}
			return self::$instance;
		}

		public function set($settings) {
			$this->settings = $settings + $this->settings;
		}

		public function get() {
			return $this->settings;
		}

		public function __construct() {
		}

		public function __clone() {
		}

		public function offsetExists($offset) {
			isset(self::$instance->settings[$offset]);
		}

		public function offsetGet($offset) {
			return self::$instance->settings[$offset];
		}

		public function offsetSet($offset, $value) {
			self::$instance->settings[$offset] = $value;
		}

		public function offsetUnset($offset) {
			unset(self::$instance->settings[$offset]);
		}
	}