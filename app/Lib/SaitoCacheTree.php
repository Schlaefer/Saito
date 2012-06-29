<?php

/**
 * @package saito_cache_tree
 */
class SaitoCacheTree extends Object {

	static protected $_cachedEntries 	= NULL;
	static protected $_isEnabled	    = FALSE;
	static protected $_isUpdated			= FALSE; 

	public function canUseCache($entry = null, $user = null) {
		if(!self::$_isEnabled) return false;
		if(empty($entry)) return false;

		if ($this->isCacheCurrent($entry)) {
			if ( $this->_isTreeOldToUser($entry, $user) ) {
				return true;
			}
		}
		return false;
	}

  public function isTreeUpdateableByUser($entry, $user) {
    if(!self::$_isEnabled) return false;

    return $this->_isTreeOldToUser($entry, $user);
  }

  protected function _isTreeOldToUser($entry, $user) {

    // … user is not logged-in, so there can't be any new …
    if ( !isset($user['last_refresh']) ):
      return TRUE;
    // … OR if he is logged-in and there are no new entries for him in this thread
    elseif ( strtotime($entry['last_answer']) < strtotime($user['last_refresh']) ):
      return TRUE;
    endif;

    return FALSE;
  }

  /**
   * Checks if the cache for an entry is available and current
   *
   * @param array $entry
   * @return boolean
   */
	public function isCacheCurrent($entry) {
		if(!self::$_isEnabled) return false;

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
		if(!self::$_isEnabled) return false;
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
		if(!self::$_isEnabled) return false;
		$data = array('time' => time(), 'content' => $content);
		self::$_cachedEntries[$id] = $data;
		self::$_isUpdated = TRUE;
	}

	public function readCache() {
		if(!self::$_isEnabled) return false;
    Stopwatch::start('SaitoCacheTree->readCache()');
		self::$_cachedEntries = Cache::read('EntrySub');
    Stopwatch::end('SaitoCacheTree->readCache()');
	}
	
	public function saveCache() {
		if( !self::$_isEnabled || self::$_isUpdated === FALSE ) return false;
		$this->_gc();
		self::$_cachedEntries['last_update']['day'] = mktime(0, 0, 0);
		Cache::write('EntrySub', (array)self::$_cachedEntries);
	}

	public static function enable() {
		self::$_isEnabled =  TRUE;
	}

	public static function disable() {
		self::$_isEnabled =  FALSE;
	}

	/**
	 * Garbage collection
	 *
	 */
	protected function _gc() {
		if(!self::$_isEnabled || !self::$_cachedEntries) return false;

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