<?php

App::import("Lib", 'SaitoCacheTree');

/**
 * @package saito_cache_tree
 */
class CacheTreeComponent extends SaitoCacheTree {

	public function initialize(&$controller, $settings=array()) {
		if ( Configure::read('debug') > 1 || Configure::read('Saito.Cache.Thread') == FALSE ) {
			self::$_forceNoCache = TRUE; 
			}

		if (	 $controller->params['action'] 	== 'mix' 	// don't cache in mix view
				|| $controller->params['action'] 	== 'view' // don't cache in index view
		) {
			self::$_forceNoCache = true;
		}
		$this->readCache();
	}

	public function beforeRedirect(&$controller) {
		$this->saveCache();
	}

	public function shutdown(&$controller) {
		$this->saveCache();
	}
}
?>