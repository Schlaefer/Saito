<?php

	namespace Saito\Cache;

	use Cake\Event\Event;
	use Cake\Event\EventListenerInterface;
	use Cake\Event\EventManager;

	class LineCacheSupportCachelet extends CacheSupportCachelet implements
		EventListenerInterface {

		protected $_title = 'LineCache';

		protected $_LineCache;

		public function __construct(ItemCache $LineCache) {
			EventManager::instance()->attach($this);
			$this->_LineCache = $LineCache;
		}

		public function implementedEvents() {
			return [
				'Model.Entry.update' => 'onEntryChanged'
			];
		}

		/**
		 * @param $event
		 * @throws InvalidArgumentException
		 */
		public function onEntryChanged(Event $event) {
			$posting = $event->data()['data'];
			$tid = $posting->get('tid');
			if (!$tid) {
				throw new \InvalidArgumentException('No thread-id in event data.');
			}
			$id = $posting->get('id');
			$this->clear($id);
		}

		public function clear($id = null) {
			if ($id === null) {
				$this->_LineCache->reset();
			} else {
				$this->_LineCache->delete($id);
			}
		}

	}
