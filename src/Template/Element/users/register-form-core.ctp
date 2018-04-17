<?php
echo $this->Form->control('username', [
    'autocomplete' => 'username',
    'label' => __('register_user_name'),
    'tabindex' => 1
]);
echo $this->Form->control('user_email', [
    'autocomplete' => 'email',
    'label' => __('register_user_email'),
    'tabindex' => 2
]);
echo $this->Form->control('password', [
    'autocomplete' => 'new-password',
    'type' => 'password',
    'div' => ['class' => 'input required'],
    'label' => __('user_pw'),
    'tabindex' => 3,
    'value' => ''
]);
echo $this->Form->control('password_confirm', [
    'autocomplete' => 'new-password',
    'type' => 'password',
    'div' => ['class' => 'input password required'],
    'label' => __('user_pw_confirm'),
    'tabindex' => 4,
    'value' => ''
]);
