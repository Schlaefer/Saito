<?php

	App::uses('Component', 'Controller');

	class ShoutsComponent extends Component {

		protected $_controller;

		public function startup(Controller $controller) {
			$this->_controller = $controller;
		}

		public function setShoutsForView() {
			$this->_controller->loadModel('Shout');
			$shouts = $this->_controller->Shout->get();
			$cached_shouts = Cache::read('Shouts.html');
			if ($cached_shouts && $shouts[0]['Shout']['id'] === $cached_shouts['lastId']) {
				$this->_controller->set('shouts', $cached_shouts['html']);
			} else {
				$this->_controller->initBbcode();
				$this->_controller->set('shouts', $this->_controller->Shout->get());
			}
		}

	}
