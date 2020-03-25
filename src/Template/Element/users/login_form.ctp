<?php
$redirect = $this->request->getQuery('redirect', null);
echo $this->Form->create(
    null,
    [
        'url' => ['_name' => 'login', '?' => ['redirect' => $redirect]],
        'id' => 'login_form',
    ]
);

$name = $this->Form->control(
    'username',
    [
        'id' => 'tf-login-username',
        'label' => __('user_name'),
        'tabindex' => 100,
        'required' => 'required',
        'autocomplete' => 'username',
        'class' => 'form-control',
    ]
);
echo $this->Html->div('form-group', $name);

$password = $this->Form->control(
    'password',
    [
        'type' => 'password',
        'label' => __('user_pw'),
        'tabindex' => 101,
        'required' => 'required',
        'autocomplete' => 'current-password',
        'class' => 'form-control',
    ]
);
echo $this->Html->div('form-group', $password);

$remember = $this->Form->control(
    'remember_me',
    [
        'label' => [
            'class' => 'form-check-label',
            'text' => __('auto_login_marking'),
            'style' => 'display: inline;',
        ],
        'type' => 'checkbox',
        'style' => 'width: auto;',
        'tabindex' => 102,
        'class' => 'form-check-input',
    ]
);
echo $this->Html->div('form-group form-check', $remember);

echo $this->Form->button(
    __('login_btn'),
    [
        'class' => 'btn btn-primary',
        'tabindex' => 103,
        'type' => 'submit',
    ]
);

echo $this->Form->end();
