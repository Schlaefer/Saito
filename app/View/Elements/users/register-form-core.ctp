<?php
  echo $this->Form->input('username', [
    'autocomplete' => 'username',
    'error' => [
      'hasAllowedChars' => __('model.user.validate.username.hasAllowedChars'),
      'isUnique' => __('error_name_reserved'),
      'notEmpty' => __('error_no_name')
    ],
    'label' => __('register_user_name'),
    'tabindex' => 1
  ]);
  echo $this->Form->input('user_email', [
    'autocomplete' => 'email',
    'error' => [
      'isUnique' => __('error_email_reserved'),
      'isEmail' => __('error_email_wrong')
    ],
    'label' => __('register_user_email'),
    'tabindex' => 2
  ]);
  echo $this->Form->input('user_password', [
    'autocomplete' => 'new-password',
    'type' => 'password',
    'div' => ['class' => 'input required'],
    'error' => [
      'notEmpty' => __('error_password_empty'),
      'validation_error_pwConfirm' => __('error_password_confirm')
    ],
    'label' => __('user_pw'),
    'tabindex' => 3
  ]);
  echo $this->Form->input('password_confirm', [
    'autocomplete' => 'new-password',
    'type' => 'password',
    'div' => ['class' => 'input password required'],
    'label' => __('user_pw_confirm'),
    'tabindex' => 4
  ]);
