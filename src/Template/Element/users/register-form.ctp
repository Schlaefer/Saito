<?php

use Cake\Core\Configure;

echo $this->Form->create(
    $user,
    ['url' => ['action' => 'register'], 'id' => 'registerForm']
);
echo $this->element('users/register-form-core');
echo $this->SimpleCaptcha->control(
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

    echo $this->Form->control(
        'tos_confirm',
        [
            'type' => 'checkbox',
            'div' => ['class' => 'input password required'],
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
            require(['jquery', 'backbone'], function ($, Backbone) {
                'use strict';
                var RegisterView = Backbone.View.extend({
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
        });
    })(SaitoApp);
</script>
