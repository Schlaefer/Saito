<?php
  echo $this->Form->input('username', [
    'autocomplete' => 'off',
    'error' => [
      'hasAllowedChars' => __('model.user.validate.username.hasAllowedChars'),
      'isUnique' => __('error_name_reserved'),
      'isUsernameEqual' => __('error.name.equalExists'),
      'notEmpty' => __('error_no_name')
    ],
    'label' => __('register_user_name'),
    'tabindex' => 1
  ]);
  echo $this->Form->input('user_email', [
    'autocomplete' => 'off',
    'error' => [
      'isUnique' => __('error_email_reserved'),
      'isEmail' => __('error_email_wrong')
    ],
    'label' => __('register_user_email'),
    'tabindex' => 2
  ]);
  echo $this->Form->input('user_password', [
    'autocomplete' => 'off',
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
    'autocomplete' => 'off',
    'type' => 'password',
    'div' => ['class' => 'input password required'],
    'label' => __('user_pw_confirm'),
    'tabindex' => 4
  ]);
