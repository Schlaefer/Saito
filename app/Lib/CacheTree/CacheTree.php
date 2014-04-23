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

		protected $_allowRead = false;

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

			$_cacheConfig = Cache::settings();
			if ($_cacheConfig['engine'] === 'Apc') {
				$this->_CacheEngine = new CacheTreeAppCacheEngine;
			} else {
				$this->_CacheEngine = new CacheTreeDbCacheEngine;
			}

			if (
					$Controller->params['controller'] === 'entries' && $Controller->params['action'] === 'index'
			) {
				$this->_allowUpdate = true;
				$this->_allowRead = true;
			}

			if (Configure::read('debug') > 1 || Configure::read('Saito.Cache.Thread') == false):
				$this->_allowUpdate = false;
				$this->_allowRead = false;
			endif;

			if ($this->_allowRead || $this->_allowUpdate) {
				$this->readCache();
			}
		}

		public function isCacheUpdatable(array $entry) {
			if ( !$this->_allowUpdate ) {
				return false;
			}
			return $this->_isEntryOldForUser($entry);
		}

		public function isCacheValid(array $entry) {
			if ( !$this->_allowRead) {
				return false;
			}

			$isCacheValid = false;

			if ( isset($this->_validEntries[$entry['id']]) ):
				return $this->_validEntries[$entry['id']];
			endif;

			if (isset($this->_cachedEntries[(int)$entry['id']]) &&
					strtotime($entry['last_answer']) <= $this->_cachedEntries[(int)$entry['id']]['metadata']['content_last_updated']) {
				if ($this->_isEntryOldForUser($entry)) {
					$isCacheValid = true;
				}
			}
			$this->_validEntries[$entry['id']] = $isCacheValid;
			return $isCacheValid;
		}

		protected function _isEntryOldForUser(array $entry) {
			if (!$this->_CurrentUser->isLoggedIn()
					|| strtotime($entry['last_answer']) < strtotime($this->_CurrentUser['last_refresh'])) {
				return true;
			}
			return false;
		}

		public function delete($id) {
			$this->_isUpdated = true;
			$this->readCache();
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

			if ( isset($this->_cachedEntries[(int)$id]) ) {
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
			$now = time();
			if (!$timestamp) {
				$timestamp = $now;
			}
			if (!$this->_allowUpdate) {
				return false;
			}
			$this->_isUpdated = true;
			$this->readCache();
			$metadata = array(
				'created' => $now,
				'content_last_updated' => $timestamp,
			);
			$data = array( 'metadata' => $metadata, 'content' => $content );
			$this->_cachedEntries[(int)$id] = $data;
		}

		public function readCache() {
			if ($this->_cachedEntries === null):
				Stopwatch::start('SaitoCacheTree->readCache()');
				$this->_cachedEntries = $this->_CacheEngine->read();

				$depractionTime = time() - $this->_CacheEngine->getDeprecationSpan();

				if (!empty($this->_cachedEntries)) {
					foreach ($this->_cachedEntries as $id => $entry) {
						if ($entry['metadata']['created'] < $depractionTime) {
							unset($this->_cachedEntries[$id]);
							$this->_isUpdated = true;
						}
					}
				}
				Stopwatch::end('SaitoCacheTree->readCache()');
			endif;
		}

		public function saveCache() {
			if ($this->_isUpdated === false) {
				return false;
			}

			$this->_gc();
			$this->_CacheEngine->write((array)$this->_cachedEntries);
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