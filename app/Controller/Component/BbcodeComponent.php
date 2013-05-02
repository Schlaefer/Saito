<?php

	App::uses('Component', 'Controller');
	App::uses('BbcodeUserlistUserModel', 'Lib/Bbcode');

	class BbcodeComponent extends Component {

		protected $_initHelper = false;

		protected $_controller;

		public $server;

		public $webroot;

		public $settings = array(
			'hashBaseUrl' => '',
			'atBaseUrl'   => ''
		);

		public function initialize(Controller $controller) {
			$this->_controller = $controller;
			$this->server = FULL_BASE_URL;
			$this->webroot = $controller->webroot;
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
			$string = preg_replace(
				"%
				(?<!=) # don't hash if part of [url=…
				{$this->server}{$this->webroot}{$this->settings['hashBaseUrl']}
				(\d+)  # the id
				%imx",
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
			$userlist = new BbcodeUserlistUserModel();
			$userlist->set($controller->User);
			$controller->helpers['Bbcode'] = array(
				'quoteSymbol' => Configure::read('Saito.Settings.quote_symbol'),
				'hashBaseUrl' => $controller->webroot . $this->settings['hashBaseUrl'],
				'atBaseUrl'   => $controller->webroot . $this->settings['atBaseUrl'],
				'UserList'    => $userlist
			);
		}
	}

