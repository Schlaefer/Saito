<?php

	/**
	 * Event-manager for Saito
	 *
	 * A thin event manager with maximum dispatch speed.  It's called a
	 * few hundred times on the front-page and gives roughly 3x over
	 * the CakeEventManager in Cake 2.
	 */
	class SaitoEventManager {

		protected static $_Instance;

		protected $_listeners = [];

		public function implementedEvents() {
			return [];
		}

		public static function getInstance() {
			if (self::$_Instance === null) {
				self::$_Instance = new SaitoEventManager();
			}
			return self::$_Instance;
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
			$this->_listeners[$key][] = $callable;
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
				return null;
			}
			foreach ($this->_listeners[$key] as $func) {
				// faster than call_user_func
				$results[] = $func[0]->$func[1]($data);
			}
//			Stopwatch::stop("SaitoEventManager::dispatch $key");
			return $results;
		}

	}