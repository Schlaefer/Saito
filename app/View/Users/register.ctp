<h1><?php echo __('register_linkname'); ?></h1>
<div class='box_layout_1 box-form'>
	<?php if ($register_success == 'email_send') : ?>
			<?php echo __('register_email_send_content'); ?>
		<?php elseif ($register_success == 'success') : ?>
			<?php echo __('register_success_content'); ?>
		<?php else : ?>
			<?php
			echo $this->Html->tag('p', __('register-js-required'),
					array(
					'id'		 => 'register-js-required',
					'class'	 => 'message',
			));
			echo $this->Html->scriptBlock('$("#register-js-required").hide();', array('inline' => true));
			?>
			<?php
			echo $this->Form->create('User', array('action' => 'register'));
			echo $this->element('users/add_form_core');
			echo $this->SimpleCaptcha->input('User',
					array(
					'error' => array(
							'captchaResultIncorrect' => __d('simple_captcha',
									'Captcha result incorrect'),
							'captchaResultTooLate'	 => __d('simple_captcha',
									'Captcha result too late'),
							'captchaResultTooFast'	 => __d('simple_captcha',
									'Captcha result too fast'),
					),
					'div'										 => array('class' => 'required'),
					)
			);
			echo $this->Form->input('tos_confirm',
					array(
					'type' => 'checkbox',
					'div'	 => array('class'	 => 'input password required'),
					'label'	 => __('register_tos_label',
							$this->Html->link(__('register_tos_linktext'),
									array(
									'controller' => 'pages',
									'action'		 => Configure::read('Config.language'),
									'tos'
									), array(
									'target' => '_blank',
									)
							)
					)
			));
			echo $this->Js->get('#UserTosConfirm')->event('click',
					<<<EOF
if (this.checked) {
	$('#btn-register-submit').removeAttr("disabled");
} else {
	$('#btn-register-submit').attr("disabled", true);
}
return true;
EOF
			);
			echo $this->Form->submit(__('register_linkname'),
					array(
					'id'			 => 'btn-register-submit',
					'class'		 => 'btn btn-submit',
					'disabled' => 'disabled',
			));
			echo $this->Form->end();
			?>
<?php endif; ?>
</div>