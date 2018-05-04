<?php
$redirect = $this->request->getQuery('redirect', null);
echo $this->Form->create(
    null,
    [
        'url' => ['_name' => 'login', '?' => ['redirect' => $redirect]],
        'id' => 'login_form'
    ]
);

echo $this->Form->control(
    'username',
    [
        'id' => 'tf-login-username',
        'label' => __('user_name'),
        'tabindex' => 100,
        'required' => 'required',
        'autocomplete' => 'username'
    ]
);

echo $this->Form->control(
    'password',
    [
        'type' => 'password',
        'label' => __('user_pw'),
        'tabindex' => 101,
        'required' => 'required',
        'autocomplete' => 'current-password'
    ]
);

echo $this->Form->control(
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
        'class' => 'btn btn-primary',
        'tabindex' => 103,
    ]
);

echo $this->Form->end();
