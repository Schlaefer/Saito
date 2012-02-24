<?php

App::uses('Sanitize', 'Utility');

// import here so that `cake schema ...` cli works
App::import('Lib', 'Stopwatch.Stopwatch');

class AppModel extends Model {

	# Entry->User->UserOnline
	public $recursive = 1;

	static $sanitize = true;

	protected static $Timer;

	public function  __construct($id = false, $table = null, $ds = null) {
		parent::__construct($id, $table, $ds);
		self::$Timer = Stopwatch::getInstance();
	}

	protected function _sanitizeFields($results) {
		if (isset($this->fieldsToSanitize)) {
			foreach ($results as $k => $result) {
				foreach ($this->fieldsToSanitize as $field) {
					if (isset($results[$k][$this->name][$field])) {
						$results[$k][$this->alias][$field] = Sanitize::html($result[$this->alias][$field]);
					}
				}
			}
		}
		return $results;
	}

	public function afterFind($results, $primary = false) {
		parent::afterFind($results, $primary);

		if (self::$sanitize) $results = $this->_sanitizeFields($results);

		return $results;
	}

	public function sanitize($switch = true) {
			self::$sanitize = $switch;
	}

	public function toggle($key) {
		$this->contain();
		$value = $this->read($key);
		$value = ($value[$this->name][$key] == 0) ? 1 : 0;
		$this->set($key, $value);
		$this->save();
		return $value;
	}

}
?>