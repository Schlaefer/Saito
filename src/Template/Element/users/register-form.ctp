<?php

use Cake\Core\Configure;

echo $this->Form->create(
    $user,
    ['url' => ['action' => 'register'], 'id' => 'registerForm']
);
echo $this->element('users/register-form-core');
echo $this->SimpleCaptcha->control(
    [
        'class' => 'form-control mb-3',
        'required' => true,
        'div' => ['class' => 'form-group'],
        'tabindex' => 10
    ]
);

if (Configure::read('Saito.Settings.tos_enabled')) {
    $tosUrl = Configure::read('Saito.Settings.tos_url');
    if (empty($tosUrl)) {
        $tosUrl = '/pages/' . Configure::read('Saito.language') . '/tos';
    };

    $tos = $this->Form->control(
        'tos_confirm',
        [
            'type' => 'checkbox',
            'class' => 'form-check-input',
            'label' => [
                'text' => __(
                    'register_tos_label',
                    $this->Html->link(
                        __('register_tos_linktext'),
                        $tosUrl,
                        ['target' => '_blank']
                    )
                ),
                'escape' => false
            ],
            'id' => 'tosConfirm',
            'tabindex' => 11
        ]
    );
    echo $this->Html->div('form-group form-check', $tos);
}

echo $this->Form->submit(
    __('register_linkname'),
    [
        'id' => 'btn-register-submit',
        'class' => 'btn btn-primary',
        'disabled' => $tosRequired ? 'disabled' : '',
        'tabindex' => 12
    ]
);
echo $this->Form->end();
?>

<script>
    (function (SaitoApp) {
        SaitoApp.callbacks.afterViewInit.push(function () {
            'use strict';
            var RegisterView = Marionette.View.extend({
                events: {
                    'click #tosConfirm': '_onTosConfirm'
                },
                _onTosConfirm: function (event) {
                    var checked = event.currentTarget.checked;
                    var submit = this.$('input[type=submit]');
                    if (checked) {
                        submit.removeAttr("disabled");
                    } else {
                        submit.attr("disabled", true);
                    }
                }
            });
            var registerForm = new RegisterView({el: '#registerForm'});
        });
    })(SaitoApp);
</script>
