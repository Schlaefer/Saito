<?php

	App::uses('Component', 'Controller');
	App::uses('BbcodeUserlistUserModel', 'Lib/Bbcode');
	App::uses('BbcodeSettings', 'Lib/Bbcode');

	class BbcodeComponent extends Component {

		protected $_initHelper = false;

		/** @var BbcodeSettings */
		protected $_settings;

		public function initialize(Controller $controller) {
			$this->_settings = new BbcodeSettings([
				'server' => Router::fullBaseUrl(),
				'webroot' => $controller->webroot
			]);
		}

		public function beforeRender(Controller $controller) {
			if ($this->_initHelper === true) {
				$this->_initHelper($controller);
			}
		}

		/**
		 * Inits the Bbcode Helper for use in a View
		 *
		 * Call this instead of including in the controller's $helpers array.
		 */
		protected function _initHelper(Controller $controller) {
			$userlist = new BbcodeUserlistUserModel();
			$userlist->set($controller->User);

			$this->_settings->add([
				'quote_symbol' => Configure::read('Saito.Settings.quote_symbol'),
				'smiliesData' => $controller->getSmilies(),
				'UserList' => $userlist
			]);

			$controller->helpers['Bbcode'] = $this->_settings->get();
		}

		public function initHelper() {
			$this->_initHelper = true;
		}

	}
