<?php

	App::uses('Model', 'Model');
	App::uses('Sanitize', 'Utility');
	App::uses('CakeEvent', 'Event');
	App::uses('SaitoEventManager', 'Lib/Saito/Event');

	// import here so that `cake schema ...` cli works
	App::import('Lib', 'Stopwatch.Stopwatch');

	class AppModel extends Model {

		/**
		 * @var array model settings; can be overwritten by DB or config Settings
		 */
		protected $_settings = [];

		/**
		 * @var array predefined fields for filterFields()
		 */
		public $allowedInputFields = [];

		# Entry->User->UserOnline
		public $recursive = 1;

		public $SharedObjects;

		/** * @var SaitoEventManager */
		protected $_SEM;

		public function __construct($id = false, $table = null, $ds = null) {
			parent::__construct($id, $table, $ds);
			$this->_dispatchEvent('Event.Saito.Model.initialize');
		}

		public function __get($name) {
			switch ($name) {
				case 'dic':
					return ClassRegistry::getObject('dic');
				default:
					if (isset($this->SharedObjects[$name])) {
						return $this->SharedObjects[$name];
					}
			}
			return parent::__get($name);
		}

		public function toggle($key) {
			$this->contain();
			$value = $this->read($key);
			$value = ($value[$this->alias][$key] == 0) ? 1 : 0;
			$this->set($key, $value);
			$this->save();
			return $value;
		}

		/**
		 * filters out all fields $fields in $data
		 *
		 * works only on current model, not associations
		 *
		 * @param $data
		 * @param $fields
		 */
		public function filterFields(&$data, $fields) {
			if (is_string($fields) && isset($this->allowedInputFields[$fields])) {
				$fields = $this->allowedInputFields[$fields];
			}
			$fields = array_flip($fields);
			$data = [
				$this->alias => array_intersect_key($data[$this->alias], $fields)
			];
		}

		public function requireFields(&$data, array $required) {
			return $this->_mapFields($data, $required,
				function (&$data, $model, $field) {
					if (!isset($data[$model][$field])) {
						return false;
					}
					return true;
				});
		}

		public function unsetFields(&$data, array $unset = ['id']) {
			return $this->_mapFields($data, $unset,
				function (&$data, $model, $field) {
					if (isset($data[$model][$field])) {
						unset($data[$model][$field]);
					}
					return true;
				});
		}

		protected function _mapFields(&$data, $fields, callable $func) {
			if (isset(reset($data)[$this->alias])) {
				foreach ($data as &$d) {
					if (!$this->_mapFields($d, $fields, $func)) {
						return false;
					}
				}
				return true;
			}

			if (!isset($data[$this->alias])) {
				$data = [$this->alias => $data];
			}
			foreach ($fields as $field) {
				if (strpos($field, '.') !== false) {
					list($model, $field) = explode('.', $field, 2);
				} else {
					$model = $this->alias;
				}
				if ($model !== $this->alias) {
					continue;
				}
				if (!$func($data, $model, $field)) {
					return false;
				}
			}
			return true;
		}

		/**
		 * Increments value of a field
		 *
		 * @param $id
		 * @param $field
		 * @param int $amount
		 * @throws InvalidArgumentException
		 */
		public function increment($id, $field, $amount = 1) {
			if (!is_int($amount)) {
				throw new InvalidArgumentException;
			}
			$operator = '+';
			if ($amount < 0) {
				$operator = '-';
				$amount *= -1;
			}
			$field = $this->alias . '.' . $field;
			$this->updateAll([$field => "$field $operator $amount"],
				[$this->alias . '.id' => $id]);
		}

		public function pipeMerger(array $data) {
			$out = [];
			foreach ($data as $key => $value) {
				$out[] = "$key=$value";
			}
			return implode(' | ', $out);
		}

		/**
		 * Splits String 'a=b|c=d|e=f' into an array('a'=>'b', 'c'=>'d', 'e'=>'f')
		 *
		 * @param string $pipeString
		 * @return array
		 */
		protected function _pipeSplitter($pipeString) {
			$unpipedArray = array();
			$ranks = explode('|', $pipeString);
			foreach ($ranks as $rank) :
				$matches = array();
				$matched = preg_match('/(\w+)\s*=\s*(.*)/', trim($rank), $matches);
				if ($matched) {
					$unpipedArray[$matches[1]] = $matches[2];
				}
			endforeach;
			return $unpipedArray;
		}

		protected static function _getIp() {
			$ip = null;
			if (Configure::read('Saito.Settings.store_ip')):
				$ip = env('REMOTE_ADDR');
				if (Configure::read('Saito.Settings.store_ip_anonymized')):
					$ip = self::_anonymizeIp($ip);
				endif;
			endif;
			return $ip;
		}

		/**
		 * Dispatches an event
		 *
		 * - Always passes the issuing model class as subject
		 * - Wrapper for CakeEvent boilerplate code
		 * - Easier to test
		 *
		 * @param string $event event identifier `Model.<modelname>.<event>`
		 * @param array $data additional event data
		 */
		protected function _dispatchEvent($event, $data = []) {
			$this->getEventManager()->dispatch(new CakeEvent($event, $this, $data));
			// propagate event on Saito's event bus
			$this->dispatchSaitoEvent($event, $data);
		}

		public function dispatchSaitoEvent($event, $data) {
			if (!$this->_SEM) {
				$this->_SEM = SaitoEventManager::getInstance();
			}
			$this->_SEM->dispatch($event, $data + ['Model' => $this]);
		}

		/**
		 * Rough and tough ip anonymizer
		 *
		 * @param string $ip
		 * @return string
		 */
		protected static function _anonymizeIp($ip) {
			$strlen = strlen($ip);
			if ($strlen > 6) :
				$divider = (int)floor($strlen / 4) + 1;
				$ip = substr_replace($ip, '…', $divider, $strlen - (2 * $divider));
			endif;

			return $ip;
		}

		/**
		 * gets app setting
		 *
		 * falls back to local definition if available
		 *
		 * @param $name
		 * @return mixed
		 * @throws UnexpectedValueException
		 */
		protected function _setting($name) {
			$setting = Configure::read('Saito.Settings.' . $name);
			if ($setting !== null) {
				return $setting;
			}
			if (isset($this->_settings[$name])) {
				return $this->_settings[$name];
			}
			throw new UnexpectedValueException;
		}

		public function isUniqueCiString($fields) {
			// lazy: only one field
			if (!is_array($fields) || count($fields) > 1) {
				throw InvalidArgumentException();
			}
			$key = key($fields);
			$fields = [
				"LOWER({$this->alias}.{$key})" => mb_strtolower(current($fields))
			];
			return $this->isUnique($fields);
		}

		/**
		 * Inclusive Validation::range()
		 *
		 * @param array $check
		 * @param float $lower
		 * @param float $upper
		 * @return bool
		 * @see https://github.com/cakephp/cakephp/issues/3304
		 */
		public function inRange($check, $lower = null, $upper = null) {
			$check = reset($check);
			if (!is_numeric($check)) {
				return false;
			}
			if (isset($lower) && isset($upper)) {
				return ($check >= $lower && $check <= $upper);
			}
			// fallback to 'parent'
			return Validation::range($check, $lower, $upper);
		}

		/**
		 * Logs current SQL log
		 *
		 * Set debug to 2 to enable SQL logging!
		 */
		public function logSql() {
			if (Configure::read('debug') < 2) {
				trigger_error('You must set debug level to at least 2 to enable SQL-logging',
					E_USER_NOTICE);
			}
			$dbo = $this->getDatasource();
			$logs = $dbo->getLog();
			$this->log($logs['log']);
		}

	}
