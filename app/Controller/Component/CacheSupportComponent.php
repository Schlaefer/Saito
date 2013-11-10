<?php

	App::uses('Component', 'Controller');
	App::import('Lib', 'CacheSupport');
	App::uses('CacheTree', 'Lib/CacheTree');

	class CacheSupportComponent extends Component {

		protected $_CacheSupport;

		public $CacheTree;

		public function initialize(Controller $Controller) {
			$this->_CacheSupport = new CacheSupport();
			$this->_addConfigureCachelets();
			$this->CacheTree = CacheTree::getInstance();
			$this->CacheTree->initialize($Controller);
		}

		/**
		 * Adds additional cachelets from Configure `Saito.Cachelets`
		 *
		 * E.g. use in `Plugin/<foo>/Config/bootstrap.php`:
		 *
		 * <code>
		 * Configure::write('Saito.Cachelets.M', ['location' => 'M.Lib', 'name' => 'MCacheSupportCachelet']);
		 * </code>
		 */
		protected function _addConfigureCachelets() {
			$_additionalCachelets = Configure::read('Saito.Cachelets');
			if (!$_additionalCachelets) {
				return;
			}
			foreach ($_additionalCachelets as $_c) {
				if (!class_exists(($_c['name']))) {
					App::uses($_c['name'], $_c['location']);
				}
				$this->_CacheSupport->add(new $_c['name']);
			}
		}

		public function beforeRender(Controller $Controller) {
			$Controller->set('CacheTree', $this->CacheTree);
		}

		public function beforeRedirect(Controller $Controller, $url, $status = null, $exit = true) {
			$this->CacheTree->saveCache();
		}

		public function shutdown(Controller $Controller) {
			$this->CacheTree->saveCache();
		}

		public function __call($method, $params) {
			$proxy = [$this->_CacheSupport, $method];
			if (is_callable($proxy)) {
				return call_user_func_array($proxy, $params);
			}
		}

	}
