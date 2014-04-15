<div class="panel">
	<?=
		$this->Layout->panelHeading(__('register_linkname'),
			['pageHeading' => true]) ?>
	<div class="panel-content">
		<?php
			if ($register_success === 'email_send') {
				$lang = Configure::read('Config.language');
				$element = 'users' . DS . $lang . DS . 'register-email_send';
				echo $this->element($element);
			} elseif ($register_success === 'success') {
				echo __('register_success_content');
			} else {
				echo $this->Html->tag('p', __('js-required'),
					['id' => 'register-js-required', 'class' => 'message',]);
				echo $this->Html->scriptBlock('$("#register-js-required").hide();',
					['inline' => true]);
				echo $this->Form->create('User', ['action' => 'register']);
				echo $this->element('users/add_form_core');
				echo $this->SimpleCaptcha->input('User', [
						'error' => [
							'captchaResultIncorrect' => __d('simple_captcha',
								'Captcha result incorrect'),
							'captchaResultTooLate' => __d('simple_captcha',
								'Captcha result too late'),
							'captchaResultTooFast' => __d('simple_captcha',
								'Captcha result too fast'),
						],
						'div' => ['class' => 'input required']
					]
				);
				if (Configure::read('Saito.Settings.tos_enabled')) {
					// set tos url
					$tos_url = Configure::read('Saito.Settings.tos_url');
					if (empty($tos_url)) {
						$tos_url = '/pages/' . Configure::read('Config.language') . '/tos';
					};

					echo $this->Form->input('tos_confirm', [
						'type' => 'checkbox',
						'div' => ['class' => 'input password required'],
						'label' => __('register_tos_label',
							$this->Html->link(__('register_tos_linktext'),
								$tos_url, ['target' => '_blank']))
					]);
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
				}

				echo $this->Form->submit(__('register_linkname'), [
					'id' => 'btn-register-submit',
					'class' => 'btn btn-submit',
					'disabled' => Configure::read('Saito.Settings.tos_enabled') ? 'disabled' : '',
				]);
				echo $this->Form->end();
			}
		?>
	</div>
</div>