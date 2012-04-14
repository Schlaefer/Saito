<h1><?php echo __('register_linkname'); ?></h1>
<div class='fieldset_2'>
	<?php  if ($register_success == 'email_send') : ?>
		Vielen Dank für Ihre Registrierung. Ihnen wurde eine Email mit weiteren Daten zugesendet. Bitte klicken Sie auf den Link in dieser Email. Vorher ist ein Einloggen ins Forum nicht möglich! <!-- @lo -->
	<?php  elseif ($register_success == 'success') : ?>
		Ihre Registrierung war erfolgreich. Falls sie noch nicht eingeloggt sind, können sie dies nun nachholen. Viel Spaß bei macnemo.de! <!-- @lo -->
	<?php  else : ?>
		<?php
		 echo $this->Form->create('User', array('action'=>'register'));
		 echo $this->element('users/add_form_core');
		 echo $this->SimpleCaptcha->input('User', 
				 array(
						 'error' => array(
								'captchaResultIncorrect' 	=> __d('simple_captcha', 'Captcha result incorrect'),
								'captchaResultTooLate' 		=> __d('simple_captcha', 'Captcha result too late'),
								'captchaResultTooFast' 		=> __d('simple_captcha', 'Captcha result too fast'),
							),
						 'div' =>  array( 'class' => 'required'),
						)
				 );
		 echo $this->Form->submit(__('register_linkname'), array( 'class'=> 'btn_submit'));
		 echo $this->Form->end();
		?>
	<?php  endif; ?>
</div>