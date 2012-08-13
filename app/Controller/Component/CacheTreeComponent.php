<?php

	App::import('Lib', 'Stopwatch.Stopwatch');
	App::uses('CacheTreeAppCacheEngine', 'Lib/CacheTree');
	App::uses('CacheTreeDbCacheEngine', 'Lib/CacheTree');
	App::uses('Component', 'Controller');

	/**
	 * @package saito_cache_tree
	 */
	class CacheTreeComponent extends Component {

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

		public function initialize(Controller $Controller) {
			$this->_CurrentUser = $Controller->CurrentUser;

			$cache_config = Cache::settings();
			if ($cache_config['engine'] === 'File') {
				$this->_CacheEngine = new CacheTreeDbCacheEngine;
			} else {
				$this->_CacheEngine = new CacheTreeAppCacheEngine;
			}

			if (
					$Controller->params['controller'] === 'entries' && $Controller->params['action'] === 'index'
			) {
				$this->_allowUpdate 	= true;
				$this->_allowRead 		= true;
			}

			if ( Configure::read('debug') > 1 || Configure::read('Saito.Cache.Thread') == FALSE ):
				$this->_allowUpdate 	= false;
				$this->_allowRead 		= false;
			endif;

			$this->readCache();
		}

		public function beforeRedirect(Controller $Controller, $url) {
			parent::beforeRedirect($Controller, $url);
			$this->saveCache();
		}

		public function beforeRender(Controller $Controller) {
			parent::beforeRender($Controller);
			$Controller->set('CacheTree', $this);
		}

		public function shutdown(Controller $Controller) {
			parent::shutdown($Controller);
			$this->saveCache();
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

			if ( isset($this->_cachedEntries[$entry['id']]) && strtotime($entry['last_answer']) < $this->_cachedEntries[$entry['id']]['time']) {
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
			$this->_isUpdated = TRUE;
			$this->readCache();
			unset($this->_cachedEntries[$id]);
		}

		public function read($id = null) {
			if ( !$this->_allowRead )
				return false;
			if ( $id === null ) {
				return $this->_cachedEntries;
			}

			if ( isset($this->_cachedEntries[$id]) ) {
				return $this->_cachedEntries[$id]['content'];
			}

			return FALSE;
		}

		public function update($id, $content) {
			if (!$this->_allowUpdate) { return false; }
			$this->_isUpdated = TRUE;
			$this->readCache();
			$data = array( 'time' => time(), 'content' => $content );
			$this->_cachedEntries[$id] = $data;
		}

		public function readCache() {
			if ( $this->_cachedEntries === NULL ):
				Stopwatch::start('SaitoCacheTree->readCache()');
				$this->_cachedEntries = $this->_CacheEngine->read();

				$depractionTime = time() - $this->_CacheEngine->getDeprecationSpan();

				if(!empty($this->_cachedEntries)) {
					foreach ($this->_cachedEntries as $id => $entry) {
						if ($entry['time'] < $depractionTime) {
							unset($this->_cachedEntries[$id]);
							$this->_isUpdated = TRUE;
						}
					}
				}
				Stopwatch::end('SaitoCacheTree->readCache()');
			endif;
		}

		public function saveCache() {
			if ( $this->_isUpdated === FALSE )
				return false;

			$this->_gc();
			$this->_CacheEngine->write((array)$this->_cachedEntries);
		}

		/**
		 * Garbage collection
		 */
		protected function _gc() {
			if ( !$this->_cachedEntries )
				return false;

			$number_of_cached_entries = count($this->_cachedEntries);
			if ( $number_of_cached_entries > $this->_maxNumberOfEntries ) {
				// descending time sort
				uasort($this->_cachedEntries, function($a, $b) {
					if ($a['time'] == $b['time']) {
						return 0;
					}
					return ($a['time'] < $b['time']) ? 1 : -1;
					});
				$this->_cachedEntries = array_slice($this->_cachedEntries, 0, $this->_maxNumberOfEntries, true);
			}
		}

	}