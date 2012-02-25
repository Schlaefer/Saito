SimpleCaptcha for CakePHP
=========================

Simple captcha plugin for CakePHP.

See: <https://github.com/Schlaefer/cakephp-simple-captcha>

Install
-------

Checkout as `simple_captcha` into your plugin directory.

Usage Example
-------------
### Helper ###

Include helper in the Controller:

	var $helpers = array (
			'SimpleCaptcha.SimpleCaptcha',
	);


### Behavior ###

Attach Behavior. E.g. dynamically in Controller:

		if (!empty($this->data)) {
			$this->User->Behaviors->attach('SimpleCaptcha');
			if ($this->User->save($this->data)) { 
				…


### Use in View ###

		 echo $this->SimpleCaptcha->input('User', 
				 array(
						 'error' => array(
								'captchaResultIncorrect' 	=> __d('simple_captcha', 'Captcha result incorrect', true),
								'captchaResultTooLate' 		=> __d('simple_captcha', 'Captcha result too late', true),
								'captchaResultTooFast' 		=> __d('simple_captcha', 'Captcha result too fast', true),
							),
						 'div' =>  array( 'class' => 'required'),
						)
				 );


