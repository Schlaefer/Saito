<?php

	App::uses('Component', 'Controller');

	class ShoutsComponent extends Component {

		public $settings = ['maxNumberOfShouts' => 10];

		protected $_Controller;

		protected $_ShoutModel = null;

		public function startup(Controller $controller) {
			$this->_Controller = $controller;
			$this->settings['maxNumberOfShouts'] = (int)Configure::read(
				'Saito.Settings.shoutbox_max_shouts');
		}

		public function setShoutsForView() {
			// @performance only do if cache is not valid and html need to be rendered
			$this->_Controller->initBbcode();
			$this->_Controller->set('shouts', $this->get());
		}

		public function get() {
			return $this->_model()->get();
		}

		public function push($data) {
			return $this->_model()->push($data);
		}

		protected function _model() {
			if ($this->_ShoutModel !== null) {
				return $this->_ShoutModel;
			}
			$this->_Controller->loadModel('Shout');
			$this->_ShoutModel = $this->_Controller->Shout;
			$this->_ShoutModel->maxNumberOfShouts = $this->settings['maxNumberOfShouts'];
			return $this->_ShoutModel;
		}

	}
