<?php

	class CacheSupport extends Object {

		protected $_Caches = [];

		public function __construct() {
			$this->addCache(
				[
					'Apc',
					'Cake',
					'Saito'
				]
			);
		}

		public function clear($cache = null, $id = null) {
			if ($cache === null) {
				foreach ($this->_Caches as $Cachelet) {
					$Cachelet->clear();
				}
			} else {
				$this->_Caches[$cache]->clear($id);
			}
		}

		public function addCache($cache) {
			if (is_array($cache)) {
				foreach ($cache as $name) {
					$this->_addCachelet($name);
				}
			} else {
				$this->_addCachelet($cache);
			}
		}

		protected function _addCachelet($cache) {
			if (!isset($this->_Caches[$cache])) {
				$cache_name = $cache . 'Cachelet';
				$this->_Caches[$cache] = new $cache_name;
			}
		}
	}


	interface Cachelets {
		public function clear($id = null);
	}

	class SaitoCachelet implements Cachelets {
		public function clear($id = null) {
			Cache::clear(false, 'default');
			Cache::clear(false, 'short');
		}
	}

	class ApcCachelet implements Cachelets {
		public function clear($id = null) {
			if (function_exists('apc_store')) {
				apc_clear_cache();
				apc_clear_cache('user');
				apc_clear_cache('opcode');
			}
		}
	}

	class CakeCachelet implements  Cachelets {
		public function clear($id = null) {
			Cache::clearGroup('persistent');
			Cache::clearGroup('models');
			Cache::clearGroup('views');
		}
	}