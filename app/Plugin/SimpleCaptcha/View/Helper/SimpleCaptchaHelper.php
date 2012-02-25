<?php

App::import('Lib', 'SimpleCaptcha.SimpleCaptcha');

/**
 * Captcha Helper
 * 
 * Output of form data for the captcha
 */
class SimpleCaptchaHelper extends AppHelper {
	var $helpers = array('Form');

	private $options = null; 
  private $methods = array('hash', 'db', 'session');

	private $captcha_operator_convert = null;

	private $captcha_text	 	= null;
	private $captcha_result = null;
	private $captcha_hash		= null;
	private $captcha_generated = false;

	function __construct(View $View, $settings = array()) {
		$this->options = SimpleCaptcha::$defaults;

		# Set up an array with the operators that we want to use. With difficulty=1 it is only subtraction and addition.
		$this->captcha_operator_convert = array( '-' );

		$configs = (array)Configure::read('Captcha');
		if (!empty($configs)) {
			$this->options = array_merge($this->options, $configs);
		}

		parent::__construct($View, $settings);
	}


	/**
	 * Generates the captcha values 
	 * 
	 * @return array 
	 */
  protected function _generate() {
		if ($this->captcha_generated === TRUE) return;
  	# Choose the first number randomly between 6 and 10. This is to stop the answer being negative.
		$numberOne = mt_rand(6, 9);
  	# Choose the second number randomly between 0 and 5.
  	$numberTwo = mt_rand(1, 5);
		# Choose the operator randomly from the array.
  	$captchaOperator = $this->captcha_operator_convert[mt_rand(0, count($this->captcha_operator_convert) - 1)];

  	# Get the equation in textual form to show to the user.
  	$this->captcha_text =  $numberOne . ' ' . $captchaOperator . ' ' . $numberTwo;

  	# Evaluate the equation and get the result.
		$this->captcha_result = $numberOne - $numberTwo;

		# Session-Way (only one form at a time) - must be a component then
    //$this->Session->write('Captcha.result', $result);

    # DB-Way (several forms possible, high security via IP-Based max limits)
    // the following should be done in a component and passed to the view/helper
    // $Captcha = ClassRegistry::init('Captcha');
    // $Captcha->new(); $Captcha->update(); etc

  	# Timestamp-SessionID-Hash-Way (several forms possible, not as secure)
  	$this->captcha_hash = SimpleCaptcha::buildHash(array('timestamp' => time(), 'result' => $this->captcha_result), $this->options);

		$this->captcha_generated = TRUE;
  	return;
  }

	/**
	 * active + passive captcha
	 * 
	 * @param type $model
	 * @param type $options
	 * @return string
	 */
	public function input($model = null, $options = array()) {
		return $this->active($model, $options) . $this->passive($model, $options);
		return $out;
	}

	/**
	 * passive captcha
	 * 2010-01-08 ms
	 */
	public function passive($model = null, $options = array()) {
		$this->_generate();

		$field = $this->__fieldName($model);

		# add passive part on active forms as well
		$out = '<div style="display:none">'.
            $this->Form->input($field.'_hash', array('value'=>$this->captcha_hash)).
            $this->Form->input($field.'_time', array('value'=>time())).
            $this->Form->input((!empty($model)?$model.'.':'').$this->options['dummyField'], array('value'=>'')).
        '</div>';

		return $out;
	}

	/**
	 * active math captcha
	 * either combined with between=true (all in this one funtion)
	 * or seperated by =false (needs input(false) and captcha() calls then)
	 * @param bool between: [default: true]
	 * 2010-01-08 ms
	 */
	public function active($model = null, $options = array()) {
		$this->_generate();

		// @todo refactor into __construct
		$defaultOptions = array(
				'type'=>'text',
				'class'=>'captcha',
				'value'=>'',
				'maxlength'=>3,
				'label'=>__('Captcha',true),
				'combined'=>true, 'autocomplete'=>'off'
			);
		$options = array_merge($defaultOptions, $options);

		$out = '';
		if ($this->options['type'] == 'active') {
			// obvuscate operation for bots by reversing the code in source but reverse effect with CSS
			$out .= '<span id="captchaCode" style="unicode-bidi: bidi-override; direction: rtl;">'.strrev($this->captcha_text);
			// let's go nuts here
			$out .= '<span style="display: none;"> - '.mt_rand(10, 20).'</span>';
			$out .= '</span>';
		}


		if ($options['combined'] === true) {
            $options['between'] = $out.' = ';
        }
		unset($options['combined']);

		return $this->Form->input($this->__fieldName($model), $options); 
	}

	private function __fieldName($model_name = null) {
		$field_name = 'captcha';
		if (isSet($model_name)) {
			$field_name = $model_name.'.'.$field_name;
		}
		return $field_name;
	}
}
?>
