<?php

	namespace App\Lib\Model\Table;

	use Cake\Core\Configure;
	use Cake\Database\Expression\QueryExpression;
	use Cake\Event\Event;
	use Cake\Event\EventManager;
	use Cake\ORM\Entity;
	use Cake\ORM\Table;
	use Saito\Event\SaitoEventManager;

	class AppTable extends Table {

		/**
		 * @var array model settings; can be overwritten by DB or config Settings
		 */
		protected $_settings = [];

		/**
		 * @var array predefined fields for filterFields()
		 */
		public $allowedInputFields = [];

		public $SharedObjects;

		/** * @var SaitoEventManager */
		protected $_SEM;

		/**
		 * @param $id
		 * @param $key
		 * @return int
		 */
		public function toggle($id, $key) {
			$entity = $this->query()
				->select(['id', $key])
				->where(['id' => $id])
				->first();
			$new = ($entity->get($key) == 0) ? 1 : 0;
			$this->patchEntity($entity, [$key => $new]);
			$this->save($entity);
			return $new;
		}

		/**
		 * @param int|array|\ArrayAccess $conditions
		 * @return bool
		 */
		public function exists($conditions) {
			if (is_int($conditions)) {
				$conditions = ['id' => $conditions];
			}
			return parent::exists($conditions);
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
			$data = array_intersect_key($data, $fields);
		}

		/**
		 * checks that all $required keys are in array $data
		 *
		 * @param array $data
		 * @param array $required
		 * @return bool false if not all required fields present, true otherwise
		 */
		public function requireFields(&$data, array $required) {
			return $this->_mapFields($data, $required,
				function (&$data, $field, $model = null) {
					if ($model === null) {
						if (!isset($data[$field])) {
							return false;
						}
					}
					/*
					 * @todo 3.0 dead code?
				else {
						if (!isset($data[$model][$field])) {
							return false;
						}
					}
					*/
						return true;
					});
			}

			/**
			 * removes all keys $unset from array $data
			 *
			 * @param array $data
			 * @param array $unset
			 * @return bool
			 */
		public function unsetFields(&$data, array $unset = ['id']) {
			return $this->_mapFields($data, $unset,
				function (&$data, $field, $model = null) {
					if ($model === null) {
						if (isset($data[$field])) {
							unset($data[$field]);
						}
					}
					/*
					 * @todo 3.0 dead code?
					else {
						if (!isset($data[$model][$field])) {
							unset($data[$model][$field]);
						}
					}
					*/
					return true;
				});
		}

		protected function _mapFields(&$data, $fields, callable $func) {
			$isArrayWithMultipleResults = isset(reset($data)[reset($fields)]);
			if ($isArrayWithMultipleResults) {
				foreach ($data as &$d) {
					if (!$this->_mapFields($d, $fields, $func)) {
						return false;
					}
				}
				return true;
			}

			/*
			 * @todo 3.0 should be dead code, remove after transition
			if (!isset($data[$this->alias()])) {
				$data = [$this->alias() => $data];
			}
			*/
			foreach ($fields as $field) {
				/*
				if (strpos($field, '.') !== false) {
					list($model, $field) = explode('.', $field, 2);
				} else {
					$model = $this->alias();
				}
				if ($model !== $this->alias()) {
					continue;
				}
				*/
				if (!$func($data, $field)) {
					return false;
				}
			}
			return true;
		}

		/**
		 * Increments value of a field
		 *
		 * @param int|array $where entry-id or array with conditions
		 * @param string $field fielt to increment
		 * @param int $amount
		 * @throws InvalidArgumentException
		 */
		public function increment($where, $field, $amount = 1) {
			if (!is_int($amount)) {
				throw new InvalidArgumentException;
			}

			if (is_int($where)) {
				$where = 	['id' => $where];
			}

			$operator = '+';
			if ($amount < 0) {
				$operator = '-';
				$amount *= -1;
			}
			$expression = new QueryExpression("$field = $field $operator $amount");
			$this->updateAll($expression, $where);
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
			EventManager::instance()->dispatch(new Event($event, $this, $data));
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
