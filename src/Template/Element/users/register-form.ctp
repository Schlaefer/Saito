<?php

use Cake\Core\Configure;

echo $this->Form->create($user, ['action' => 'register']);
echo $this->element('users/register-form-core');
echo $this->SimpleCaptcha->input(
    [
        'required' => true,
        'div' => ['class' => 'input required'],
        'tabindex' => 10
    ]
);

  if (Configure::read('Saito.Settings.tos_enabled')) {
    $tosUrl = Configure::read('Saito.Settings.tos_url');
    if (empty($tosUrl)) {
      $tosUrl = '/pages/' . Configure::read('Saito.language') . '/tos';
    };

      echo $this->Form->input(
          'tos_confirm',
          [
              'type' => 'checkbox',
              'div' => ['class' => 'input password required'],
              'label' => [
                  'text' => __(
                      'register_tos_label',
                      $this->Html->link(
                          __('register_tos_linktext'),
                          $tosUrl, ['target' => '_blank']
                      )
                  ),
                  'escape' => false
              ],
              'tabindex' => 11
          ]
      );
      // @todo 3.0
    ?>
      <!---
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
    -->
    <?php
  }

  echo $this->Form->submit(__('register_linkname'), [
    'id' => 'btn-register-submit',
    'class' => 'btn btn-submit',
    'disabled' => $tosRequired ? 'disabled' : '',
    'tabindex' => 12
  ]);
  echo $this->Form->end();
