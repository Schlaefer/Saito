<?php

App::import('Lib', 'SaitoCacheTree');
App::uses('AppHelper', 'View/Helper');

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

  public function __call($method, $params) {
    if ( method_exists($this->_CacheTree, $method) ):
      return call_user_func_array(array($this->_CacheTree, $method), $params);
    else:
      parent::__call($method, $params);
    endif;
  }

}

?>