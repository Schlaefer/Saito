<?php

		echo $this->Form->create( 'User',
				array('url' => '/users/login', 'id' => 'login_form' ));
		echo $this->Form->input(
			'username',
			array(
					'id'			 => 'tf-login-username',
					'label'		 => __('user_name'),
					'value'		 => '',
					'tabindex' => 1,
			)
	);
	echo $this->Form->input(
			'password',
			array(
					'type' => 'password',
					'label'	 => __('user_pw'),
					'value'	 => '',
					'tabindex' => 2,
					)
			);
		echo $this->Form->input( 
				'remember_me',
				array(
						'label' => array(
								'text' => __('auto_login_marking'),
								'style' => 'display: inline;',
								),
						'type' => 'checkbox', 'style' =>'width: auto;',
						'tabindex' => 3,
						)
				);
		echo $this->Form->submit(
				__('login_btn'),
				array(
						'class' => 'btn btn-submit',
						'tabindex' => 4,
						)
				);
		echo $this->Form->end();
?>