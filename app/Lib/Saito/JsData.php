<?php

	namespace Saito;

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

		public function set($key, $value) {
			$this->_appJs[$key] = $value;
		}

		public function addAppJsMessage($message, $options = null) {
			$defaults = array(
				'type' => 'notice',
				'channel' => 'notification'
			);
			if (is_string($options)) {
				$defaults['type'] = $options;
				$options = array();
			}
			$options = array_merge($defaults, $options);

			if (!is_array($message)) {
				$message = array($message);
			}

			foreach ($message as $m) {
				$nm = array(
					'message' => $m,
					'type' => $options['type'],
					'channel' => $options['channel']
				);
				if (isset($options['title'])) {
					$nm['title'] = $options['title'];
				}
				if (isset($options['element'])) {
					$nm['element'] = $options['element'];
				}
				$this->_appJs['msg'][] = $nm;
			}
		}

		public function getAppJsMessages() {
			return array('msg' => $this->_appJs['msg']);
		}

	}