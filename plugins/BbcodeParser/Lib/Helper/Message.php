<?php

	namespace Plugin\BbcodeParser\Lib\Helper;

	class Message {

		protected $_message = '';

		public function reset() {
			$this->_message = '';
		}

		public function set($message) {
			$this->_message = $message;
		}

		public function get() {
			return self::format($this->_message);
		}

		public static function format($message) {
			return "<div class='richtext-imessage'>$message</div>";
		}

	}
