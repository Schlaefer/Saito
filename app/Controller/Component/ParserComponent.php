<?php

	App::uses('Component', 'Controller');
	App::uses('SaitoUserUserlistUserModel', 'Lib/SaitoUser/Userlist');

	class ParserComponent extends Component {

		/** @var SaitoMarkupSettings */
		protected $_settings;

		public function initialize(Controller $controller) {
			// is needed in Markup Behavior
			$this->_settings = new Saito\Markup\Settings([
				'server' => Router::fullBaseUrl(),
				'webroot' => $controller->webroot
			]);
		}

		public function beforeRender(Controller $controller) {
				$this->_initHelper($controller);
		}

		/**
		 * Inits the ParserHelper for use in a View
		 *
		 * Call this instead of including in the controller's $helpers array.
		 */
		protected function _initHelper(Controller $controller) {
			$userlist = new SaitoUserUserlistUserModel();
			$userlist->set($controller->User);
			$smilies = new \Saito\Smiley\Cache($controller);
			$controller->set('smiliesData', $smilies);

			$this->_settings->add([
				'quote_symbol' => Configure::read('Saito.Settings.quote_symbol'),
				'smiliesData' => $smilies,
				'UserList' => $userlist
			]);

			$controller->helpers['Parser'] = $this->_settings->get();
		}

	}
