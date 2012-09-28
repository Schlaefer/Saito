<?php

	App::uses('Model', 'Model');
App::uses('Sanitize', 'Utility');

// import here so that `cake schema ...` cli works
App::import('Lib', 'Stopwatch.Stopwatch');

class AppModel extends Model {

	# Entry->User->UserOnline
	public $recursive = 1;

	static $sanitize = true;

	protected $_CurrentUser;

	public function setCurrentUser(SaitoUser $CurrentUser) {
		$this->_CurrentUser = $CurrentUser;
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

		if (self::$sanitize) {
			$results = $this->_sanitizeFields($results);
		} else {
			// sanitizing can only be disabled for one request
			$this->sanitize(true);
		}
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

  /**
   * Splits String 'a=b|c=d|e=f' into an array('a'=>'b', 'c'=>'d', 'e'=>'f')
   * 
   * @param string $pipeString
   * @return array
   */
  protected function _pipeSplitter($pipeString) {
    $unpipedArray = array();
    $ranks = explode("|", $pipeString);
    foreach ( $ranks as $rank ) :
      $matches = array();
      $matched = preg_match('/(\d+)\s*=\s*(.*)/', trim($rank), $matches);
      if ($matched) {
        $unpipedArray[$matches[1]] = $matches[2];
      }
    endforeach;
    return $unpipedArray;
  }

  protected static function _getIp() {
    $ip = NULL;
    if ( Configure::read('Saito.Settings.store_ip') ):
      $ip = env('REMOTE_ADDR');
      if ( Configure::read('Saito.Settings.store_ip_anonymized' ) ):
        $ip = self::_anonymizeIp($ip);
      endif;
    endif;
    return $ip;
  }

  /**
   * Rough and tough ip anonymizer
   *
   * @param string $ip
   * @return string
   */
  protected static function _anonymizeIp($ip) {
    $strlen = strlen($ip);
    if ( $strlen > 6 ) :
      $divider = (int)floor($strlen / 4) + 1;
      $ip = substr_replace($ip, '…', $divider, $strlen - (2 * $divider));
    endif;

    return $ip;
  }

}