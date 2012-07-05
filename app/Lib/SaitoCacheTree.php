<?php

	App::import('Lib', 'Stopwatch.Stopwatch');

	/**
	 * @package saito_cache_tree
	 */
	class SaitoCacheTree extends Object {


		/**
		 * Stores if an entry is cached and if the cache is valid for this request
		 *
		 * The value is used at least two times: once in the DB and once in the View.
		 * So we cache it here after it is created for the first time.
		 *
		 * This saves as a few function calls and strtotime() at the moment
		 *
		 * @var array
		 */
		static protected $_validEntries = array( );

		static protected $_cachedEntries = NULL;
		static protected $_isEnabled = FALSE;
		static protected $_isUpdated = FALSE;

		/**
		 * Determines if an entry is cached and
		 * @param array $entry
		 * @param type $invalidBefore unix timestamp
		 * @return type
		 */
		public function isEntryCached(array $entry, $invalidBefore) {
			if ( !self::$_isEnabled ) {
				return false;
			}

			$isEntryCached = false;

			if ( isset(self::$_validEntries[$entry['id']][$invalidBefore]) ):
				return self::$_validEntries[$entry['id']][$invalidBefore];
			endif;

			if ( $this->isEntryCachedAfter($entry['id'], strtotime($entry['last_answer'])) ) {
				if ( $this->isEntryCachedBefore($entry['id'], $invalidBefore) ) {
					$isEntryCached = true;
				}
			}
			self::$_validEntries[$entry['id']] = $isEntryCached;
			return $isEntryCached;
		}

		public function isEntryCachedBefore($id, $timestamp = NULL) {
			return $this->_compareDates($id, $timestamp, 'before');
		}

		public function isEntryCachedAfter($id, $timestamp = NULL) {
			return $this->_compareDates($id, $timestamp, 'after');
		}

		protected function _compareDates($id, $timestamp, $compare) {
			if ( !self::$_isEnabled || empty($timestamp) ) {
				return false;
			}

			$out = false;

			if ( !empty(self::$_cachedEntries[$id]) ) {
				if ( $compare === 'after' && $timestamp < self::$_cachedEntries[$id]['time'] ) {
					$out = true;
				} elseif ( $compare === 'before' && $timestamp > self::$_cachedEntries[$id]['time'] ) {
					$out = true;
				}
			};

			return $out;
		}

		public function delete($id) {
			self::$_isUpdated = TRUE;
			$this->readCache();
			unset(self::$_cachedEntries[$id]);
		}

		public function read($id = null) {
			if ( !self::$_isEnabled )
				return false;
			if ( $id === null ) {
				return self::$_cachedEntries;
			}

			if ( isset(self::$_cachedEntries[$id]) ) {
				return self::$_cachedEntries[$id]['content'];
			}

			return FALSE;
		}

		public function update($id, $content) {
			self::$_isUpdated = TRUE;
			$this->readCache();
			$data = array( 'time' => time(), 'content' => $content );
			self::$_cachedEntries[$id] = $data;
		}

		public function readCache() {
			if ( !self::$_isEnabled )
				return false;
			if ( self::$_cachedEntries === NULL ):
				Stopwatch::start('SaitoCacheTree->readCache()');
				self::$_cachedEntries = Cache::read('EntrySub');
				Stopwatch::end('SaitoCacheTree->readCache()');
			endif;
		}

		public function saveCache() {
			if ( self::$_isUpdated === FALSE )
				return false;

			// store current state
			$is_enabled = self::$_isEnabled;
			self::$_isEnabled = TRUE;

			$this->_gc();
			self::$_cachedEntries['last_update']['day'] = mktime(0, 0, 0);
			Cache::write('EntrySub', (array)self::$_cachedEntries);

			// restore state
			self::$_isEnabled = $is_enabled;
		}

		public static function enable() {
			self::$_isEnabled = TRUE;
		}

		public static function disable() {
			self::$_isEnabled = FALSE;
		}

		/**
		 * Garbage collection
		 *
		 */
		protected function _gc() {
			if ( !self::$_isEnabled || !self::$_cachedEntries )
				return false;

			// unset cache ad midnight (relative dates)
			if ( isset(self::$_cachedEntries['last_update']['day']) && mktime(0, 0, 0) != self::$_cachedEntries['last_update']['day'] ) {
				self::$_cachedEntries = array( );
			}

			$cache_config = Cache::settings();
			$depraction_time = time() - $cache_config['duration'];

			foreach ( self::$_cachedEntries as $id => $entry ) {
				if ( isset($entry['time']) && $entry['time'] < $depraction_time ) {
					$this->delete($id);
				}
			}
		}

	}

?>