<?php

	App::uses('Component', 'Controller');
	App::import('Lib', 'CacheSupport');

	class CacheSupportComponent extends Component {

		public $components = [
			'CacheTree'
		];

		protected $_CacheSupport;

		public function __construct(ComponentCollection $collection, $settings = array()) {
			$this->_CacheSupport = new CacheSupport();
			parent::__construct($collection, $settings);
		}

		public function initialize(Controller $Controller) {
			$this->CacheTree->initialize($Controller);
		}

		public function beforeRedirect(Controller $Controller, $url, $status = null, $exit = true) {
			$this->CacheTree->beforeRedirect($Controller, $url, $status, $exit);
		}

		public function shutdown(Controller $Controller) {
			$this->CacheTree->shutdown($Controller);
		}

		public function clearAll() {
			$this->_CacheSupport->clear();
			$this->clearTree();
		}

		public function clearTree($id = null) {
			Cache::clear(false, 'entries');
			if ($id === null) {
				$this->CacheTree->reset();
			} else {
				$this->CacheTree->delete($id);
			}
		}

		/**
		 * Clears out the APC if available
		 */
		public function clearApc() {
			$this->_CacheSupport->clear('Apc');
		}
	}
