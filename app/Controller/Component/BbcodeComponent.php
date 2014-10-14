<?php

	App::uses('Component', 'Controller');
	App::uses('BbcodeUserlistUserModel', 'Lib/Bbcode');
	App::uses('BbcodeSettings', 'Lib/Bbcode');

	class BbcodeComponent extends Component {

		protected $_initHelper = false;

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
			$settings = BbcodeSettings::getInstance();

			$userlist = new BbcodeUserlistUserModel();
			$userlist->set($controller->User);

			$controller->helpers['Bbcode'] = [
				'quoteSymbol' => Configure::read('Saito.Settings.quote_symbol'),
				'hashBaseUrl' => $controller->webroot . $settings['hashBaseUrl'],
				'atBaseUrl' => $controller->webroot . $settings['atBaseUrl'],
				'smiliesData' => $controller->getSmilies(),
				'UserList' => $userlist
			];
		}

		public function initHelper() {
			$this->_initHelper = true;
		}

	}
