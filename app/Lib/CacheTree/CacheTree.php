<?php

	App::uses('CacheTreeAppCacheEngine', 'Lib/CacheTree');
	App::uses('CacheTreeDbCacheEngine', 'Lib/CacheTree');

	class CacheTree {

		private static $__instance = null;

/**
 * Stores if an entry is cached and if the cache is valid for this request
 *
 * @var array
 */
		protected $_validEntries = array();

		protected $_cachedEntries = null;

/**
 * Max number of stored entries
 *
 * @var integer
 */
		protected $_maxNumberOfEntries = 240;

		protected $_CurrentUser;

		protected $_allowUpdate = false;

		protected $_CacheEngine = null;

		/**
		 * @var bool
		 */
		protected $_allowRead = false;

		/**
		 * @var bool
		 */
		protected $_isUpdated = false;

		public static function getInstance() {
			if (self::$__instance === null) {
				$name = get_called_class();
				self::$__instance = new $name;
			}
			return self::$__instance;
		}

		protected function __construct() {
		}

		private function __clone() {
		}

		public function initialize(Controller $Controller) {
			$this->_CurrentUser = $Controller->CurrentUser;

			if ($Controller->params['controller'] === 'entries' &&
					$Controller->params['action'] === 'index'
			) {
				$this->_allowRead = true;
			}
		}

		protected function _init() {
			if (Configure::read('debug') > 1) {
				Configure::write('Saito.Cache.Thread', false);
			}

			if (Configure::read('Saito.Cache.Thread') === false) {
				return;
			}

			$this->_allowUpdate = true;
			$this->_allowRead = true;

			$this->_loadCache();
		}

		public function isCacheUpdatable(array $entry) {
			if (!$this->_allowUpdate) {
				return false;
			}
			return $this->_isEntryOldForUser($entry);
		}

		public function isCacheValid(array $entry) {
			if (!$this->_allowRead) {
				return false;
			}

			$id = (int)$entry['id'];
			if (isset($this->_validEntries[$id])) {
				return $this->_validEntries[$id];
			}

			if (!$this->_inCache($entry)) {
				$valid = false;
			} elseif ($this->_isEntryOldForUser($entry)) {
				$valid = true;
			} else {
				$valid = false;
			}

			$this->_validEntries[$id] = $valid;
			return $valid;
		}

		protected function _isEntryOldForUser(array $entry) {
			$noValidUser = !$this->_CurrentUser->isLoggedIn();
			if ($noValidUser) {
				return true;
			}

			$marUninitialized = $this->_CurrentUser['last_refresh'] === null;
			if ($marUninitialized) {
				return false;
			}

			$isNewToUser = strtotime($entry['last_answer']) < $this->_CurrentUser['last_refresh_unix'];
			if ($isNewToUser) {
				return true;
			}

			return false;
		}

		protected function _inCache($entry) {
			$id = $entry['id'];

			$isInCache = isset($this->_cachedEntries[$id]);
			if (!$isInCache) {
				return false;
			}

			$hasNewerAnswers = strtotime($entry['last_answer']) > $this->_cachedEntries[$id]['metadata']['content_last_updated'];
			if ($hasNewerAnswers) {
				$this->delete($id);
				return false;
			}

			return true;
		}

		public function delete($id) {
			$this->_isUpdated = true;
			$this->_loadCache();
			unset($this->_cachedEntries[(int)$id]);
		}

		public function reset() {
			$this->_isUpdated = true;
			$this->_cachedEntries = [];
		}

		public function read($id = null) {
			if (!$this->_allowRead) {
				return false;
			}
			if ($id === null) {
				return $this->_cachedEntries;
			}

			if (isset($this->_cachedEntries[(int)$id])) {
				return $this->_cachedEntries[(int)$id]['content'];
			}

			return false;
		}

		/**
		 * Puts an entry into the cache
		 *
		 * @param $id
		 * @param $content
		 * @param null $timestamp
		 * @return bool
		 */
		public function update($id, $content, $timestamp = null) {
			if (!$this->_allowUpdate) {
				return false;
			}
			$now = time();
			if (!$timestamp) {
				$timestamp = $now;
			}
			$this->_isUpdated = true;
			$this->_loadCache();
			$metadata = [
				'created' => $now,
				'content_last_updated' => $timestamp,
			];
			$data = ['metadata' => $metadata, 'content' => $content];
			$this->_cachedEntries[(int)$id] = $data;
		}

		protected function _engine() {
			if ($this->_CacheEngine === null) {
				$_cacheConfig = Cache::settings();
				if ($_cacheConfig['engine'] === 'Apc') {
					$this->_CacheEngine = new CacheTreeAppCacheEngine;
				} else {
					$this->_CacheEngine = new CacheTreeDbCacheEngine;
				}
			}

			return $this->_CacheEngine;
		}

		protected function _loadCache() {
			if ($this->_cachedEntries !== null) {
				return;
			}
			Stopwatch::start('SaitoCacheTree->readCache()');
			$this->_cachedEntries = $this->_engine()->read();

			if (empty($this->_cachedEntries)) {
				return;
			}

			$deprecationSpan = time() - $this->_engine()->getDeprecationSpan();
			foreach ($this->_cachedEntries as $id => $entry) {
				if ($entry['metadata']['created'] < $deprecationSpan) {
					unset($this->_cachedEntries[$id]);
					$this->_isUpdated = true;
				}
			}
			Stopwatch::end('SaitoCacheTree->readCache()');
		}

		public function save() {
			if ($this->_isUpdated === false) {
				return false;
			}

			$this->_gc();
			$this->_engine()->write((array)$this->_cachedEntries);
		}

		/**
		 * Garbage collection
		 *
		 * Remove old entries from the cache.
		 */
		protected function _gc() {
			if (!$this->_cachedEntries) {
				return false;
			}

			$_numberOfCachedEntries = count($this->_cachedEntries);
			if ($_numberOfCachedEntries > $this->_maxNumberOfEntries) {
				// descending time sort
				uasort($this->_cachedEntries, function($a, $b) {
						if ($a['metadata']['content_last_updated'] == $b['metadata']['content_last_updated']) {
							return 0;
						}
						return ($a['metadata']['content_last_updated'] < $b['metadata']['content_last_updated']) ? 1 : -1;
				});
				$this->_cachedEntries = array_slice($this->_cachedEntries, 0, $this->_maxNumberOfEntries, true);
			}
		}

	}