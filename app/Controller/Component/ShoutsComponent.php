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
			if (empty($shouts)) {
				$this->_controller->set('shouts', null);
			} else {
				$cachedShouts = Cache::read('Shouts.html');
				if ($cachedShouts && $shouts[0]['Shout']['id'] === $cachedShouts['lastId']) {
					$this->_controller->set('shouts', $cachedShouts['html']);
				} else {
					$this->_controller->initBbcode();
					$this->_controller->set('shouts', $shouts);
				}
			}
		}

	}
