<?php

App::import("Lib", 'SaitoCacheTree');

/**
 * @package saito_cache_tree
 */
class CacheTreeComponent extends Component {

	protected $_CacheTree;

	public function initialize($controller) {
		$this->_CacheTree = new SaitoCacheTree();

		if ( $controller->params['action'] 	== 'index' ) {
			SaitoCacheTree::enable();
		}

		if ( Configure::read('debug') > 1 || Configure::read('Saito.Cache.Thread') == FALSE ):
			SaitoCacheTree::disable();
    endif;

		$this->_CacheTree->readCache();
	}

	public function beforeRedirect($controller) {
		$this->_CacheTree->saveCache();
	}

	public function shutdown($controller) {
		$this->_CacheTree->saveCache();
	}

}
?>