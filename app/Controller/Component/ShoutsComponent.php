<?php

	App::uses('Component', 'Controller');

	class ShoutsComponent extends Component {

		protected $_controller;

		protected $_maxNumberOfShouts;

		protected $_ShoutModel;

		public function startup(Controller $controller) {
			$this->_controller = $controller;
			$this->_maxNumberOfShouts = (int)Configure::read(
				'Saito.Settings.shoutbox_max_shouts'
			);
		}

		public function setShoutsForView() {
			// @performance only do if cache is not valid and html need to be rendered
			$this->_controller->initBbcode();
			$shouts = $this->get();
			$this->_controller->set('shouts', $shouts);
		}

		public function push($data) {
			if (!$this->_ShoutModel) {
				$this->_load();
			}
			return $this->_ShoutModel->push($data);
		}

		public function get() {
			if (!$this->_ShoutsModel) {
				$this->_load();
			}
			$shouts = $this->_ShoutModel->get();
			return $shouts;
		}

/**
 * Loads and initializes the model
 */
		protected function _load() {
			$this->_controller->loadModel('Shout');
			$this->_ShoutModel = $this->_controller->Shout;
			$this->_ShoutModel->maxNumberOfShouts = $this->_maxNumberOfShouts;
		}

	}
