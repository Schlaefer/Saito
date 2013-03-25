<?php

	App::uses('Component', 'Controller');

	class BbcodeComponent extends Component {

		public $_initHelper = false;

		public function beforeRender(Controller $controller) {
			if ($this->_initHelper === true) {
				$this->_initHelper($controller);
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
			$controller->helpers['Bbcode'] = array(
				'quoteSymbol' => Configure::read('Saito.Settings.quote_symbol'),
				'hashBaseUrl' => $controller->webroot . 'entries/view/',
				'atBaseUrl'   => $controller->webroot . 'users/name/',
				'atUserList'  => $controller->User->find(
					'list',
					array('fields' => 'username')
				)
			);
		}
	}
