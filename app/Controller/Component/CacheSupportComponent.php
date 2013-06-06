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
			$this->clearTrees();
			$this->clearApc();
			$this->clearCake();
		}

		public function clearCake() {
			Cache::clear(false);
			Cache::clear(false, 'perf-cheat');
			Cache::clearGroup('postings');
			Cache::clearGroup('persistent');
			Cache::clearGroup('models');
		}

		public function clearTree($id) {
			Cache::clearGroup('postings');
			$this->CacheTree->delete($id);
		}

		public function clearTrees() {
			$this->CacheTree->reset();
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
