<?php
echo $this->Form->create(null, ['id' => 'login_form']);

echo $this->Form->input(
    'username',
    [
        'id' => 'tf-login-username',
        'label' => __('user_name'),
        'tabindex' => 100,
        'required' => 'required',
        'autocomplete' => 'username'
    ]
);

echo $this->Form->input(
    'password',
    [
        'type' => 'password',
        'label' => __('user_pw'),
        'tabindex' => 101,
        'required' => 'required',
        'autocomplete' => 'current-password'
    ]
);

echo $this->Form->input(
    'remember_me',
    [
        'label' => [
            'text' => __('auto_login_marking'),
            'style' => 'display: inline;',
        ],
        'type' => 'checkbox',
        'style' => 'width: auto;',
        'tabindex' => 102,
    ]
);

echo $this->Form->submit(
    __('login_btn'),
    [
        'class' => 'btn btn-submit',
        'tabindex' => 103,
    ]
);

echo $this->Form->end();
