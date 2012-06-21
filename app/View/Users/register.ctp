<h1><?php echo __('register_linkname'); ?></h1>
<div class='box_layout_1 box-form'>
	<?php  if ($register_success == 'email_send') : ?>
    <?php echo __('register_email_send_content'); ?>
	<?php  elseif ($register_success == 'success') : ?>
    <?php echo __('register_success_content'); ?>
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
		 echo $this->Form->submit(__('register_linkname'), array( 'class'=> 'btn btn-submit'));
		 echo $this->Form->end();
		?>
	<?php  endif; ?>
</div>