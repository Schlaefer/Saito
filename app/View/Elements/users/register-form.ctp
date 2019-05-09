<?php

  echo $this->Form->create(
    'User',
    ['url' => ['controller' => 'users', 'action' => 'register']]
  );
  echo $this->element('users/register-form-core');
  echo $this->SimpleCaptcha->input('User', [
      'error' => [
        'captchaResultIncorrect' => __d('simple_captcha',
          'Captcha result incorrect'),
        'captchaResultTooLate' => __d('simple_captcha',
          'Captcha result too late'),
        'captchaResultTooFast' => __d('simple_captcha',
          'Captcha result too fast'),
      ],
      'div' => ['class' => 'input required'],
      'tabindex' => 10
    ]
  );

  if (Configure::read('Saito.Settings.tos_enabled')) {
    $tosUrl = Configure::read('Saito.Settings.tos_url');
    if (empty($tosUrl)) {
      $tosUrl = '/pages/' . Configure::read('Config.language') . '/tos';
    };

    echo $this->Form->input('tos_confirm', [
      'type' => 'checkbox',
      'div' => ['class' => 'input password required'],
      'label' => __('register_tos_label',
        $this->Html->link(__('register_tos_linktext'),
          $tosUrl, ['target' => '_blank'])),
      'tabindex' => 11
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
    'disabled' => $tosRequired ? 'disabled' : '',
    'tabindex' => 12
  ]);
  echo $this->Form->end();
