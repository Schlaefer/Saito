<?php

	App::uses('Model', 'Model');
	App::uses('Sanitize', 'Utility');
	App::uses('SaitoUser', 'Lib/SaitoUser');
	App::uses('CakeEvent', 'Event');

	// import here so that `cake schema ...` cli works
	App::import('Lib', 'Stopwatch.Stopwatch');

	class AppModel extends Model {

		protected $_settings = [];

		# Entry->User->UserOnline
		public $recursive = 1;

		public $SharedObjects;

		public function toggle($key) {
			$this->contain();
			$value = $this->read($key);
			$value = ($value[$this->alias][$key] == 0) ? 1 : 0;
			$this->set($key, $value);
			$this->save();
			return $value;
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
			if ( Configure::read('Saito.Settings.store_ip') ):
				$ip = env('REMOTE_ADDR');
				if ( Configure::read('Saito.Settings.store_ip_anonymized' ) ):
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

	}