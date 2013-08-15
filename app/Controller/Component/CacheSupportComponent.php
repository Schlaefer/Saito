<?php

	App::uses('Component', 'Controller');
	App::import('Lib', 'CacheSupport');
	App::uses('CacheTree', 'Lib/CacheTree');

	class CacheSupportComponent extends Component {

		protected $_CacheSupport;

		public $CacheTree;

		public function initialize(Controller $Controller) {
			$this->_CacheSupport = new CacheSupport();
			$this->CacheTree = CacheTree::getInstance();
			$this->CacheTree->initialize($Controller);
		}

		public function beforeRender(Controller $Controller) {
			$Controller->set('CacheTree', $this->CacheTree);
		}

		public function beforeRedirect(Controller $Controller, $url, $status = null, $exit = true) {
			$this->CacheTree->saveCache();
		}

		public function shutdown(Controller $Controller) {
			$this->CacheTree->saveCache();
		}

		public function __call($method, $params) {
			$proxy = [$this->_CacheSupport, $method];
			if (is_callable($proxy)) {
				return call_user_func_array($proxy, $params);
			}
		}
	}
