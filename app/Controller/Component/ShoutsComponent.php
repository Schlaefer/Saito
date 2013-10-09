<?php

	App::uses('Component', 'Controller');

	class ShoutsComponent extends Component {

		protected $_controller;

		protected $_maxNumberOfShouts;

		protected $_Shouts;

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

		public function get() {
			if (!$this->_Shouts) {
				$this->_load();
			}
			$shouts = $this->_Shout->get();
			return $shouts;
		}

/**
 * Loads and initializes the model
 */
		public function _load() {
			$this->_controller->loadModel('Shout');
			$this->_Shout = $this->_controller->Shout;
			$this->_Shout->maxNumberOfShouts = $this->_maxNumberOfShouts;
		}
	}
