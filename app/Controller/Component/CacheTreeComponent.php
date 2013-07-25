<?php

	App::import('Lib', 'Stopwatch.Stopwatch');
	App::uses('CacheTree', 'Lib/CacheTree');
	App::uses('Component', 'Controller');

	/**
	 * @package saito_cache_tree
	 */
	class CacheTreeComponent extends Component {

		protected $_CacheTree;

		public function initialize(Controller $Controller) {
			$this->_CacheTree = CacheTree::getInstance();
			$this->_CacheTree->initialize($Controller);
		}

		public function beforeRedirect(Controller $Controller, $url, $status = null, $exit = true) {
			$this->saveCache();
		}

		public function beforeRender(Controller $Controller) {
			$Controller->set('CacheTree', $this->_CacheTree);
		}

		public function shutdown(Controller $Controller) {
			$this->saveCache();
		}

		public function __call($method, $params) {
			$proxy = [$this->_CacheTree, $method];
			if (is_callable($proxy)) {
				return call_user_func_array($proxy, $params);
			}
		}
	}