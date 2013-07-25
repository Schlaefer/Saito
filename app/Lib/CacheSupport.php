<?php

	class CacheSupport extends Object {

		protected $_Caches = [];

		public function __construct() {
			$this->addCache(
				[
					'Apc'    => 'ApcCacheSupportCachelet',
					'Cake'   => 'CakeCacheSupportCachelet',
					'Saito'  => 'SaitoCacheSupportCachelet',
					'Thread' => 'ThreadCacheSupportCachelet'
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
			foreach ($cache as $key => $class_name) {
				$this->_addCachelet($key, new $class_name);
			}
		}

		protected function _addCachelet($key, CacheSupportCacheletInterface $cachelet) {
			if (!isset($this->_Caches[$key])) {
				$this->_Caches[$key] = $cachelet;
			}
		}
	}

	interface CacheSupportCacheletInterface {
		public function clear($id = null);
	}

	App::uses('CacheTree', 'Lib/CacheTree');
	class ThreadCacheSupportCachelet implements CacheSupportCacheletInterface {
		protected $_CacheTree;

		public function __construct() {
			$this->_CacheTree = CacheTree::getInstance();
		}

		public function clear($id = null) {
			Cache::clear(false, 'entries');
			if ($id === null) {
				$this->_CacheTree->reset();
			} else {
				$this->_CacheTree->delete($id);
			}
		}
	}

	class SaitoCacheSupportCachelet implements CacheSupportCacheletInterface {
		public function clear($id = null) {
			Cache::clear(false, 'default');
			Cache::clear(false, 'short');
		}
	}

	class ApcCacheSupportCachelet implements CacheSupportCacheletInterface {
		public function clear($id = null) {
			if (function_exists('apc_store')) {
				apc_clear_cache();
				apc_clear_cache('user');
				apc_clear_cache('opcode');
			}
		}
	}

	class CakeCacheSupportCachelet implements  CacheSupportCacheletInterface {
		public function clear($id = null) {
			Cache::clearGroup('persistent');
			Cache::clearGroup('models');
			Cache::clearGroup('views');
		}
	}