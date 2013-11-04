<?php

	App::uses('Component', 'Controller');
	App::uses('CakeEvent', 'Event');
	App::uses('CakeEventListener', 'Event');

	abstract class NotificationComponent extends Component implements CakeEventListener {

		protected $_Controller;

		protected $_Esevent;

		protected $_events;

		protected static $_closedConnection = false;

		/**
		 * @var array [<event> => <method to handle event>, …]
		 */
		protected $_handledEvents = [];

		public function startup(Controller $Controller) {
			parent::startup($Controller);
			$this->_Esevent = ClassRegistry::init(array('class' => 'Esevent'));
			CakeEventManager::instance()->attach($this);
			$this->_Controller = $Controller;
			register_shutdown_function(array(&$this, 'initProcess'));
		}

		public function implementedEvents() {
			$_handledEvents = array_fill_keys(array_keys($this->_handledEvents),
				'dispatch');
			return $_handledEvents;
		}

		protected function _closeConnection() {
			if (static::$_closedConnection === true) {
				return;
			}
			static::$_closedConnection = true;
			return;
		}

		public function dispatch($event) {
			$this->_events[] = $event;
		}

		public function initProcess() {
			if (empty($this->_events)) {
				return;
			}
			$this->_closeConnection();
			foreach ($this->_events as $event) {
				$this->_process($event);
			}
		}

		abstract protected function _process($event);

	}