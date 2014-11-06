<?php

	App::uses('Component', 'Controller');
	App::import('Lib/Cache', 'CacheSupport');
	App::uses('CacheTree', 'Lib/Cache');
	App::uses('CacheTreeCacheSupportCachelet', 'Lib/Cache');
	App::uses('SaitoCacheEngineDbCache', 'Lib/Cache');
	App::uses('SaitoCacheEngineAppCache', 'Lib/Cache');
	App::uses('ItemCache', 'Lib/Cache');
	App::uses('LineCacheSupportCachelet', 'Lib/Cache');

	class CacheSupportComponent extends Component {

		protected $_CacheSupport;

		public $CacheTree;

		public $LineCache;

		public function initialize(Controller $Controller) {
			$this->_CacheSupport = new CacheSupport();
			if ($Controller->modelClass) {
				$Controller->{$Controller->modelClass}->SharedObjects['CacheSupport'] = $this->_CacheSupport;
			}
			$this->_addConfigureCachelets();
			$this->_initCacheTree($Controller);
			$this->_initLineCache($Controller);
		}

		protected function _initLineCache() {
			$this->LineCache = new ItemCache(
				'Saito.LineCache',
				new SaitoCacheEngineAppCache,
				// duration: update relative time values in HTML at least every hour
				['duration' => 3600, 'maxItems' => 500]
			);
			$this->_CacheSupport->add(new LineCacheSupportCachelet($this->LineCache));
		}

		protected function _initCacheTree($Controller) {
			$cacheConfig = Cache::settings();
			if ($cacheConfig['engine'] === 'Apc') {
				$CacheEngine = new SaitoCacheEngineAppCache;
			} else {
				$CacheEngine = null;
			}

			$this->CacheTree = new CacheTree(
				'EntrySub',
				$CacheEngine,
				['maxItems' => 240]
			);

			$this->CacheTree->initialize($Controller->CurrentUser);
			$this->_CacheSupport->add(new CacheTreeCacheSupportCachelet($this->CacheTree));
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
				App::uses($_c['name'], $_c['location']);
				$this->_CacheSupport->add(new $_c['name']);
			}
		}

		public function beforeRender(Controller $Controller) {
			$Controller->set('LineCache', $this->LineCache);
		}

		public function __call($method, $params) {
			$proxy = [$this->_CacheSupport, $method];
			if (is_callable($proxy)) {
				return call_user_func_array($proxy, $params);
			}
		}

	}
