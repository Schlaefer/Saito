<?php

	App::import('Lib/Cache', 'CacheSupport');
	App::uses('CakeEvent', 'Event');
	App::uses('CakeEventListener', 'Event');

	class EntriesCacheSupportCachelet extends CacheSupportCachelet implements CakeEventListener {

		protected $_title = 'EntriesCache';

		protected $_CacheTree;

		public function __construct() {
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
			$this->clear();
		}

		/**
		 * @param $event
		 * @throws InvalidArgumentException
		 */
		public function onEntryChanged($event) {
			$this->clear();
		}

		public function clear($id = null) {
			Cache::clearGroup('entries', 'entries');
		}

	}

