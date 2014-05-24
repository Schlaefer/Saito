<?php
  echo $this->Form->input('username', [
    'error' => [
      'hasAllowedChars' => __('model.user.validate.username.hasAllowedChars'),
      'isUnique' => __('error_name_reserved'),
      'notEmpty' => __('error_no_name')
    ],
    'label' => __('register_user_name')
  ]);
  echo $this->Form->input('user_email', [
    'error' => [
      'isUnique' => __('error_email_reserved'),
      'isEmail' => __('error_email_wrong')
    ],
    'label' => __('register_user_email')
  ]);
  echo $this->Form->input('user_password', [
    'type' => 'password',
    'div' => ['class' => 'input required'],
    'error' => [
      'notEmpty' => __('error_password_empty'),
      'validation_error_pwConfirm' => __('error_password_confirm')
    ],
    'label' => __('user_pw')
  ]);
  echo $this->Form->input('password_confirm', [
    'type' => 'password',
    'div' => ['class' => 'input password required'],
    'label' => __('user_pw_confirm')
  ]);
