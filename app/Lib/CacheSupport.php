<?php

	class CacheSupport extends Object {

		protected $_Caches = [];

		public function __construct() {
			$this->addCache(
				[
					// php caches
					'Apc' => 'ApcCacheSupportCachelet',
					'OpCache' => 'OpCacheSupportCachelet',
					// application caches
					'Cake' => 'CakeCacheSupportCachelet',
					'Saito' => 'SaitoCacheSupportCachelet',
					'Thread' => 'ThreadCacheSupportCachelet'
				]
			);
		}

		/**
		 * @param mixed	$cache cache to clear
		 * 				null: all
		 * 				string: name of specific cache
		 * 				array: multiple name strings
		 * @param null $id
		 */
		public function clear($cache = null, $id = null) {
			if (is_array($cache)) {
				foreach ($cache as $_c) {
					$this->clear($_c, $id);
				}
			}
			if ($cache === null) {
				foreach ($this->_Caches as $_Cache) {
					$_Cache->clear();
				}
			} else {
				$this->_Caches[$cache]->clear($id);
			}
		}

		public function addCache($cache) {
			foreach ($cache as $key => $_className) {
				$this->_addCachelet($key, new $_className);
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

/**
 * @param $event
 * @throws InvalidArgumentException
 */
		public function onEntryChanged($event) {
			$_modelAlias = $event->subject()->alias;
			if (!isset($event->data['data'][$_modelAlias]['tid'])) {
				throw new InvalidArgumentException('No thread-id in event data.');
			}
			$_threadId = $event->data['data'][$_modelAlias]['tid'];
			$this->clear($_threadId);
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

	class OpCacheSupportCachelet implements CacheSupportCacheletInterface {

		public function clear($id = null) {
			if (function_exists('opcache_reset')) {
				opcache_reset();
			}
		}

	}

	class CakeCacheSupportCachelet implements CacheSupportCacheletInterface {

		public function clear($id = null) {
			Cache::clearGroup('persistent');
			Cache::clearGroup('models');
			Cache::clearGroup('views');
		}

	}