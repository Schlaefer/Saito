<?php

	namespace Saito\Cache;

	\App::uses('CakeEvent', 'Event');
	\App::uses('CakeEventListener', 'Event');

	class LineCacheSupportCachelet extends CacheSupportCachelet implements
		\CakeEventListener {

		protected $_title = 'LineCache';

		protected $_LineCache;

		public function __construct(ItemCache $LineCache) {
			\CakeEventManager::instance()->attach($this);
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
		public function onEntryChanged($event) {
			$_modelAlias = $event->subject()->alias;
			if (!isset($event->data['data'][$_modelAlias]['tid'])) {
				throw new \InvalidArgumentException('No thread-id in event data.');
			}
			$id = $event->data['data'][$_modelAlias]['id'];
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
