<?php

	App::uses('Component', 'Controller');

	class BbcodeComponent extends Component {

		protected $_initHelper = false;

		protected $_controller;

		public $settings = array(
			'hashBaseUrl' => '',
			'atBaseUrl'   => ''
		);

		public function initialize(Controller $controller) {
			$this->_controller = $controller;
		}

		public function beforeRender(Controller $controller) {
			if ($this->_initHelper === true) {
				$this->_initHelper($this->_controller);
			}
		}

		public function initHelper() {
			$this->_initHelper = true;
		}

		public function prepareInput($string) {
			$string = $this->_hashInternalEntryLinks($string);
			return $string;
		}

		protected function _hashInternalEntryLinks($string) {
			$server = $this->_controller->request->serverroot;
			$webroot = $this->_controller->webroot;
			$string = preg_replace(
				"#(?<!=){$server}{$webroot}{$this->settings['hashBaseUrl']}(\d+)#im",
				"#\\1",
				$string);
			return $string;
		}

		/**
		 * Inits the Bbcode Helper for use in a View
		 *
		 * Call this instead of including in the controller's $helpers array.
		 */
		protected function _initHelper(Controller $controller) {
			$controller->helpers['Bbcode'] = array(
				'quoteSymbol' => Configure::read('Saito.Settings.quote_symbol'),
				'hashBaseUrl' => $controller->webroot . $this->settings['hashBaseUrl'],
				'atBaseUrl'   => $controller->webroot . $this->settings['atBaseUrl'],
				'atUserList'  => $controller->User->find(
					'list',
					array('fields' => 'username')
				)
			);
		}
	}
