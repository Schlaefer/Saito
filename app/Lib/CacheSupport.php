<?php

	class CacheSupport extends Object {

		protected $_Caches = [];

		protected $_buildInCaches = [
			// php caches
			'ApcCacheSupportCachelet',
			'OpCacheSupportCachelet',
			// application caches
			'CakeCacheSupportCachelet',
			'SaitoCacheSupportCachelet',
			'ThreadCacheSupportCachelet'
		];

		public function __construct() {
			foreach ($this->_buildInCaches as $_name) {
				$this->add(new $_name);
			}
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
				return;
			}
			if ($cache === null) {
				foreach ($this->_Caches as $_Cache) {
					$_Cache->clear();
				}
			} else {
				$this->_Caches[$cache]->clear($id);
			}
		}

		public function add(CacheSupportCacheletInterface $cache, $id = null) {
			if ($id === null) {
				$id = $cache->getId();
			}
			if (!isset($this->_Caches[$id])) {
				$this->_Caches[$id] = $cache;
			}
		}

	}

	interface CacheSupportCacheletInterface {

		public function clear($id = null);

		public function getId();

	}

	abstract class CacheSupportCachelet implements CacheSupportCacheletInterface {

		public function getId() {
			return str_replace('CacheSupportCachelet', '', get_class($this));
		}

	}

	App::uses('CakeEvent', 'Event');
	App::uses('CakeEventListener', 'Event');
	App::uses('CacheTree', 'Lib/CacheTree');

	class ThreadCacheSupportCachelet extends CacheSupportCachelet implements
		CakeEventListener {

		protected $_title = 'Thread';

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
				'Model.Category.delete' => 'onThreadsReset',
				'Model.User.username.change' => 'onThreadsReset'
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

	class SaitoCacheSupportCachelet extends CacheSupportCachelet {

		protected $_title = 'Saito';

		public function clear($id = null) {
			Cache::clear(false, 'default');
			Cache::clear(false, 'short');
		}

	}

	class ApcCacheSupportCachelet extends CacheSupportCachelet {

		protected $_title = 'Apc';

		public function clear($id = null) {
			if (function_exists('apc_store')) {
				apc_clear_cache();
				apc_clear_cache('user');
				apc_clear_cache('opcode');
			}
		}

	}

	class OpCacheSupportCachelet extends CacheSupportCachelet {

		protected $_title = 'OpCache';

		public function clear($id = null) {
			if (function_exists('opcache_reset')) {
				opcache_reset();
			}
		}

	}

	class CakeCacheSupportCachelet extends CacheSupportCachelet {

		protected $_title = 'Cake';

		public function clear($id = null) {
			Cache::clearGroup('persistent');
			Cache::clearGroup('models');
			Cache::clearGroup('views');
		}

	}