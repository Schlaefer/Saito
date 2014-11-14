<?php

App::import('Lib', 'SimpleCaptcha.SimpleCaptcha');

/**
 * Captcha Behavior
 *
 * Implements the Behavior for the Captcha which validates the captcha data
 */
class SimpleCaptchaBehavior extends ModelBehavior {

	private $defaults = array(
			/**
			 * Minimum time in seconds which is considered necessary for a human to fill the form
			 *
			 * We assume that only a bot is able to fill and answer the form faster.
			 */
			'minTime' => 6,
			/**
			 * Maximum time in seconds the form is valid.
			 *
			 * Prevents harvesting hashs for later use.
			 */
			'maxTime' => 1200,
			'log' => false,
	);

	private $methods = array('hash', 'db', 'session');
	private $method = 'hash';
	private $log = false;
	private $error = '';
	private $internalError = '';

	//private $types = array('passive','active','both');
	//private $useSession = false;

	/**
	 * Setup instance
	 *
	 * @param type $Model
	 * @param type $settings
	 */
	public function setup(Model $Model, $settings = array()) {
		$this->defaults = array_merge(SimpleCaptcha::$defaults, $this->defaults);

		# bootstrap configs
		$configs = (array) Configure::read('Captcha');
		if (!empty($configs)) {
			$this->settings = array_merge($this->settings, $configs);
		}

		if (!isset($this->settings[$Model->alias])) {
			$this->settings[$Model->alias] = $this->defaults;
		}
		$this->settings[$Model->alias] = array_merge($this->settings[$Model->alias], (array) $settings);
	}

	/**
	 * Callback which initializes all the captcha related checking
	 *
	 * @param Model $Model
	 * @return bool
	 */
	public function beforeValidate(Model $Model, $options = array()) {
		$this->Model = &$Model;

		if (!$this->_validateCaptchaMinTime($this->Model->data[$this->Model->name])) {
			$this->Model->invalidate('captcha', 'captchaResultTooFast', true);
		} elseif (!$this->_validateCaptchaMaxTime($this->Model->data[$this->Model->name])) {
			$this->Model->invalidate('captcha', 'captchaResultTooLate', true);
		} elseif (!$this->_validateDummyField($this->Model->data[$this->Model->name])) {
			$this->Model->invalidate('captcha', 'captchaIllegalContent', true);
		} elseif ($this->settings[$this->Model->alias]['type'] == 'active' && !$this->_validateCaptcha($this->Model->data[$this->Model->name])) {
			$this->Model->invalidate('captcha', 'captchaResultIncorrect', true);
		}

		unset($this->Model->data[$this->Model->name]['captcha']);
		unset($this->Model->data[$this->Model->name]['captcha_hash']);
		unset($this->Model->data[$this->Model->name]['captcha_time']);

		return true;
	}

	/**
	 * Validates the dummy field
	 *
	 * @param array $data
	 * @return bool
	 */
	protected function _validateDummyField($data) {
		$dummyField = $this->settings[$this->Model->alias]['dummyField'];
		if (!empty($data[$dummyField])) {
			# dummy field not empty - SPAM!
			return $this->error('Illegal content', 'DummyField = \'' . $data[$dummyField] . '\'');
		}
		return true;
	}

	/**
	 * Validates the minimum time
	 *
	 * @param array $data
	 * @return bool
	 */
	protected function _validateCaptchaMinTime($data) {
		if ($this->settings[$this->Model->alias]['minTime'] <= 0) {
			return true;
		}

		if (isSet($data['captcha_hash']) && isSet($data['captcha_time'])
				&& ( $data['captcha_time'] < time() - $this->settings[$this->Model->alias]['minTime'] )
		) {
			return true;
		}

		return false;
	}

	/**
	 * validates maximum time
	 *
	 * @param array $data
	 * @return bool
	 */
	protected function _validateCaptchaMaxTime($data) {
		if ($this->settings[$this->Model->alias]['maxTime'] <= 0) {
			return true;
		}
		if (isSet($data['captcha_hash']) && isSet($data['captcha_time'])
				&& ( $data['captcha_time'] + $this->settings[$this->Model->alias]['maxTime'] > time() )
		) {
			return true;
		}

		return false;
	}

	/**
	 * Validates captcha calculation
	 *
	 * flood protection by false fields and math code
	 * TODO: build in floodProtection (max Trials etc)
	 * TODO: SESSION based one as alternative
	 *
	 * @param array $data
	 * @return bool
	 */
	protected function _validateCaptcha($data) {
		if (!isset($data['captcha'])) {
			# form inputs missing? SPAM!
			return $this->error('Captcha content missing');
		}

		$captcha_params = array(
				'timestamp' => $data['captcha_time'],
				'result' => $data['captcha'],
		);
		$hash = SimpleCaptcha::buildHash($captcha_params, $this->settings[$this->Model->alias]);

		if ($data['captcha_hash'] == $hash) {
			return true;
		}
		# wrong captcha content or session expired
		return $this->error('Captcha incorrect', 'SubmittedResult = \'' . $data['captcha'] . '\'');
	}

	/**
	 * return error message (or empty string if none)
	 * @return string
	 */
	public function errors() {
		return $this->error;
	}

	/**
	 * only neccessary if there is more than one request per model
	 * 2009-12-18 ms
	 */
	public function reset() {
		$this->error = '';
	}

	/**
	 * build and log error message
	 * 2009-12-18 ms
	 */
	private function error($msg = null, $internalMsg = null) {
		if (!empty($msg)) {
			$this->error = $msg;
		}
		if (!empty($internalMsg)) {
			$this->internalError = $internalMsg;
		}

		if ($this->log) {
			$this->logAttempt();
		}
		return false;
	}

	/**
	 * logs attempts
	 * @param bool errorsOnly (only if error occured, otherwise always)
	 * @returns null if not logged, true otherwise
	 * 2009-12-18 ms
	 */
	private function logAttempt($errorsOnly = true) {
		if ($errorsOnly === true && empty($this->error) && empty($this->internalError)) {
			return null;
		}

		App::import('Component', 'RequestHandler');
		$msg = 'Ip \'' . RequestHandlerComponent::getClientIP() . '\', Agent \'' . env('HTTP_USER_AGENT') . '\', Referer \'' . env('HTTP_REFERER') . '\', Host-Referer \'' . RequestHandlerComponent::getReferer() . '\'';
		if (!empty($this->error)) {
			$msg .= ', ' . $this->error;
		}
		if (!empty($this->internalError)) {
			$msg .= ' (' . $this->internalError . ')';
		}
		$this->log($msg, 'captcha');
		return true;
	}

}
?>