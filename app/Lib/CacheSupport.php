<?php

	class CacheSupport extends Object {

		protected $_Caches = [];

		public function __construct() {
			$this->addCache(
				[
					'Apc'    => 'ApcCacheSupportCachelet',
					'Cake'   => 'CakeCacheSupportCachelet',
					'Saito'  => 'SaitoCacheSupportCachelet',
					'Thread' => 'ThreadCacheSupportCachelet'
				]
			);
		}

		public function clear($cache = null, $id = null) {
			if ($cache === null) {
				foreach ($this->_Caches as $Cachelet) {
					$Cachelet->clear();
				}
			} else {
				$this->_Caches[$cache]->clear($id);
			}
		}

		public function addCache($cache) {
			foreach ($cache as $key => $class_name) {
				$this->_addCachelet($key, new $class_name);
			}
		}

		protected function _addCachelet($key, CacheSupportCacheletInterface $cachelet) {
			if (!isset($this->_Caches[$key])) {
				$this->_Caches[$key] = $cachelet;
			}
		}
	}

	interface CacheSupportCacheletInterface {
		public function clear($id = null);
	}

	App::uses('CakeEvent', 'Event');
	App::uses('CakeEventListener', 'Event');
	App::uses('CacheTree', 'Lib/CacheTree');
	class ThreadCacheSupportCachelet implements CacheSupportCacheletInterface,
		CakeEventListener {

		protected $_CacheTree;

		public function __construct() {
			$this->_CacheTree = CacheTree::getInstance();
			CakeEventManager::instance()->attach($this);
		}

		public function implementedEvents() {
			return [
				'Model.Thread.reset' => 'onThreadsReset',
				'Model.Thread.change' => 'onThreadChanged',
				'Model.Entry.replyToEntry' => 'onEntryChanged',
				'Model.Entry.update' => 'onEntryChanged',
				'Model.Category.update' => 'onThreadsReset',
				'Model.Category.delete' => 'onThreadsReset'
			];
		}

		public function onThreadsReset($event) {
			$this->clear();
		}

		public function onThreadChanged($event) {
			$this->clear($event->data['subject']);
		}

		public function onEntryChanged($event) {
			$model_alias = $event->subject()->alias;
			if (!isset($event->data['data'][$model_alias]['tid'])) {
				throw new InvalidArgumentException('No thread-id in event data.');
			}
			$thread_id = $event->data['data'][$model_alias]['tid'];
			$this->clear($thread_id);
		}

		public function clear($id = null) {
			Cache::clear(false, 'entries');
			if ($id === null) {
				$this->_CacheTree->reset();
			} else {
				$this->_CacheTree->delete($id);
			}
		}
	}

	class SaitoCacheSupportCachelet implements CacheSupportCacheletInterface {
		public function clear($id = null) {
			Cache::clear(false, 'default');
			Cache::clear(false, 'short');
		}
	}

	class ApcCacheSupportCachelet implements CacheSupportCacheletInterface {
		public function clear($id = null) {
			if (function_exists('apc_store')) {
				apc_clear_cache();
				apc_clear_cache('user');
				apc_clear_cache('opcode');
			}
		}
	}

	class CakeCacheSupportCachelet implements  CacheSupportCacheletInterface {
		public function clear($id = null) {
			Cache::clearGroup('persistent');
			Cache::clearGroup('models');
			Cache::clearGroup('views');
		}
	}