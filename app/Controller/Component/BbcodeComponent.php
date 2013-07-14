<?php

	App::uses('Component', 'Controller');
	App::uses('BbcodeUserlistUserModel', 'Lib/Bbcode');
	App::uses('BbcodeSettings', 'Lib/Bbcode');

	class BbcodeComponent extends Component {

		protected $_initHelper = false;

		protected $_controller;

		public $settings = null;

		public function initialize(Controller $controller) {
			$this->_controller = $controller;
			$this->settings = new BbcodeSettings();
		}

		public function beforeRender(Controller $controller) {
			if ($this->_initHelper === true) {
				$this->_initHelper($this->_controller);
			}
		}

		public function initHelper() {
			$this->_initHelper = true;
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

