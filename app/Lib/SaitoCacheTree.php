<?php

/**
 * @package saito_cache_tree
 */
class SaitoCacheTree extends Object {

	static protected $_cachedEntries 	= null;
	static protected $_forceNoCache		= false;
	static protected $_isUpdated			= FALSE; 

	public function canUseCache($entry = null, $user = null) {
		if(self::$_forceNoCache) return false;
		if(empty($entry)) return false;

		if ($this->isCacheCurrent($entry)) {
			if ( // … user is anonymous …
					(!isset($user['last_refresh']))
					// … OR if he is logged in there are no new postings for him
					|| (isset($user['last_refresh']) && (strtotime($entry['last_answer']) < strtotime($user['last_refresh'])))
			) {
				return true;
			}
		}
		return false;
	}

	public function isCacheCurrent($entry) {
		if(self::$_forceNoCache) return false;

		$id = $entry['id'];
		$time = strtotime($entry['last_answer']);

		// if cached thread is available AND the cache file is up to date …
		if ( !empty(self::$_cachedEntries[$id]) && $time < self::$_cachedEntries[$id]['time'] ) {
			return true;
		}
		return false;
	} // isCacheCurrent()

	public function delete($id) {
		unset(self::$_cachedEntries[$id]);
		self::$_isUpdated = TRUE;
	}

	public function read($id = null) {
		if(self::$_forceNoCache) return false;
		if ($id === null) {
			return self::$_cachedEntries;
		}

		if (isset(self::$_cachedEntries[$id])) {
			return self::$_cachedEntries[$id]['content'];
		} else {
			return false;
		}
	}

	public function update($id, $content) {
		if(self::$_forceNoCache) return false;
		$data = array('time' => time(), 'content' => $content);
		self::$_cachedEntries[$id] = $data;
		self::$_isUpdated = TRUE;
	}

	public function readCache() {
		if(self::$_forceNoCache) return false;
		self::$_cachedEntries = Cache::read('EntrySub');
	}
	
	public function saveCache() {
		if( self::$_forceNoCache || self::$_isUpdated === FALSE ) return false;
		$this->_gc();
		self::$_cachedEntries['last_update']['day'] = mktime(0, 0, 0);
		Cache::write('EntrySub', (array)self::$_cachedEntries);
	}

	public static function forceCache() {
		self::$_forceNoCache =  FALSE;
	}

	public static function forceNoCache() {
		self::$_forceNoCache =  TRUE;
	}

	/**
	 * Garbage collection
	 *
	 */
	protected function _gc() {
		if(self::$_forceNoCache || !self::$_cachedEntries) return false;

		// unset cache ad midnight (relative dates)
		if (isset(self::$_cachedEntries['last_update']['day']) && mktime(0, 0, 0) != self::$_cachedEntries['last_update']['day']) {
			self::$_cachedEntries = array();
		}

		$cache_config = Cache::settings();
		$depraction_time = time() - $cache_config['duration'];

		foreach(self::$_cachedEntries as $id => $entry) {
			if(isset($entry['time']) && $entry['time'] < $depraction_time) {
				$this->delete($id);
			}
		}
	} // end _gc();
}
?>