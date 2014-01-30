<?php
	echo $this->Form->input('username',
			array(
					'error' => array(
							'isUnique' => __('error_name_reserved'),
							'notEmpty' => __('error_no_name'),
					),
					'label' => __('register_user_name'),
			));
	echo $this->Form->input('user_email',
			array(
					'error' => array(
							'isUnique' => __('error_email_reserved'),
							'isEmail' => __('error_email_wrong'),
					),
					'label' => __('register_user_email'),
			));
	echo $this->Form->input('user_password',
			array(
					'type' => 'password',
					'div' => ['class' => 'required'],
					'error' => array(
							'notEmpty' => __('error_password_empty'),
							'validation_error_pwConfirm' => __('error_password_confirm'),
					),
					'label' => __('user_pw'),
			));
	echo $this->Form->input('password_confirm',
			array(
					'type' => 'password',
					'div' => array('class' => 'input password required'),
					'label' => __('user_pw_confirm'),
			));
