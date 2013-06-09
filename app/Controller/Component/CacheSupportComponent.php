<?php

	App::uses('Component', 'Controller');

	class CacheSupportComponent extends Component {

		public $components = [
			'CacheTree'
		];

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
			$this->clearSaito();
			$this->clearApc();
			$this->clearCake();
		}

		public function clearSaito() {
			Cache::clear(false, 'default');
			Cache::clear(false, 'short');
			$this->clearTrees();
		}

		public function clearCake() {
			Cache::clearGroup('persistent');
			Cache::clearGroup('models');
			Cache::clearGroup('views');
		}

		public function clearTree($id) {
			$this->_clearEntries();
			$this->CacheTree->delete($id);
		}

		public function clearTrees() {
			$this->_clearEntries();
			$this->CacheTree->reset();
		}

		protected function _clearEntries() {
			Cache::clear(false, 'entries');
		}

		/**
		 * Clears out the APC if available
		 */
		public function clearApc() {
			if (function_exists('apc_store')) {
				apc_clear_cache();
				apc_clear_cache('user');
				apc_clear_cache('opcode');
			}
		}

	}
