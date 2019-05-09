<?php

	/**
	 * Event-manager for Saito
	 *
	 * A thin event manager with maximum dispatch speed.  It's called a
	 * few hundred times on the front-page and gives roughly 3x over
	 * the CakeEventManager in Cake 2.
	 */
	class SaitoEventManager implements CakeEventListener {

		protected static $_Instance;

		protected $_listeners = [];

		public static function getInstance() {
			if (self::$_Instance === null) {
				self::$_Instance = new SaitoEventManager();
			}
			return self::$_Instance;
		}

		public function implementedEvents() {
			return [
				'Controller.initialize' => 'cakeEventPassThrough',
				'View.beforeRender' => 'cakeEventPassThrough'
			];
		}

		public function cakeEventPassThrough($event) {
			$data = ($event->data) ? : [];
			$data += ['subject' => $event->subject];
			$name = 'Event.Saito.' . $event->name();
			$this->dispatch($name, $data);
		}

		/**
		 * attaches event-listener
		 *
		 * @param string|SaitoEventListener $key
		 * @param null $callable function if $key is set
		 * @throws InvalidArgumentException
		 */
		public function attach($key, $callable = null) {
			if ($key instanceof SaitoEventListener) {
				foreach ($key->implementedSaitoEvents() as $eventKey => $callable) {
					$this->attach($eventKey, [$key, $callable]);
				}
				return;
			}
			if (empty($key)) {
				throw new InvalidArgumentException;
			}
			$this->_listeners[$key][] = [
				'func' => $callable,
				'type' => gettype($callable) === 'array' ? 'object' : 'closure'
			];
		}

		/**
		 * dispatches event
		 *
		 * @param string $key
		 * @param array $data
		 * @return array|null
		 */
		public function dispatch($key, $data = []) {
//			Stopwatch::start("SaitoEventManager::dispatch $key");
			if (!isset($this->_listeners[$key])) {
//				Stopwatch::stop("SaitoEventManager::dispatch $key");
				return [];
			}
			$results = [];
			foreach ($this->_listeners[$key] as $listener) {
				if ($listener['type'] === 'object') {
					// faster than call_user_func
					$result = $listener['func'][0]->{$listener['func'][1]}($data);
				} else {
					$result = $listener['func']($data);
				}
				if ($result !== null) {
					$results[] = $result;
				}
			}
//			Stopwatch::stop("SaitoEventManager::dispatch $key");
			return $results;
		}

	}