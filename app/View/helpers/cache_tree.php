<?php

App::import('Lib', 'SaitoCacheTree');

/**
 * @package saito_cache_tree
 */
class CacheTreeHelper extends SaitoCacheTree {
	var $helpers = array();

	function canCacheBeUpdated($entry, $user) {
		// if user is anonymous or user has no new entries in this thread
		if (!$this->canUseCache($entry, $user)
				&& (!$user || strtotime($entry['last_answer']) < strtotime($user['last_refresh']))) {
			return true;
		}
		return false;
	}
}

?>