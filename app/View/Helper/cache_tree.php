<?php

App::import('Lib', 'SaitoCacheTree');

/**
 * @package saito_cache_tree
 */
class CacheTreeHelper extends AppHelper {

	public $helpers = array();

	protected $_CacheTree;

	public function __construct(View $View, $settings = array()) {
		parent::__construct($View, $settings);
		$this->_CacheTree = new SaitoCacheTree();
	}

	public function canCacheBeUpdated($entry, $user) {
		// if user is anonymous or user has no new entries in this thread
		if (!$this->_CacheTree->canUseCache($entry, $user)
				&& (!$user || strtotime($entry['last_answer']) < strtotime($user['last_refresh']))) {
			return true;
		}
		return false;
	}

	public function update($id, $content) {
		$this->_CacheTree->update($id, $content);
	}

	public function canUseCache($entry, $user) {
		$this->_CacheTree->canUseCache($entry, $user);
	}
}

?>