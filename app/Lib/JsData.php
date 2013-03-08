<?php

	class JsData {

		private static $__instance = null;

		protected $_appJs = array(
			'msg' => array()
		);

		protected function __construct() {
		}

		protected function __clone() {
		}

		public static function getInstance() {
			if (self::$__instance === null) {
				self::$__instance = new JsData();
			}
			return self::$__instance;
		}

		public function getJs() {
			return $this->_appJs;
		}

		public function addAppJsMessage($message, $type = 'info') {
			if (!is_array($message)) {
				$message = array($message);
			}

			foreach ($message as $m) {
				$this->_appJs['msg'][] = array(
					'message' => $m,
					'type' => $type
				);
			}
		}

		public function getAppJsMessages() {
			return array('msg' => $this->_appJs['msg']);
		}

	}