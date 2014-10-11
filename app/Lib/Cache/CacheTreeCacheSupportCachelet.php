<?php

	App::import('Lib/Cache', 'CacheSupport');
	App::uses('CakeEvent', 'Event');
	App::uses('CakeEventListener', 'Event');

	class CacheTreeCacheSupportCachelet extends CacheSupportCachelet implements CakeEventListener {

		protected $_title = 'Thread';

		protected $_CacheTree;

		public function __construct(CacheTree $CacheTree) {
			$this->_CacheTree = $CacheTree;
			CakeEventManager::instance()->attach($this);
		}

		public function implementedEvents() {
			return [
				'Model.Thread.change' => 'onThreadChanged',
				'Model.Entry.replyToEntry' => 'onEntryChanged',
				'Model.Entry.update' => 'onEntryChanged'
			];
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
			Cache::clearGroup('entries', 'entries');
			if ($id === null) {
				$this->_CacheTree->reset();
			} else {
				$this->_CacheTree->delete($id);
			}
		}

	}

