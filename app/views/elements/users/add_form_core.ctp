<?
		 echo $form->input('username', array (
				 'error'	=> array (
						 'isUnique'	=> __('error_name_reserved', true),
						 'notEmpty'	=> __('error_no_name', true),
				 ),
				 'label'		=> __('register_user_name',true),
		 ));
		 echo $form->input('user_email', array (
				 'error'	=> array (
						 'isUnique'	=> __('error_email_reserved', true),
						 'isEmail'	=> __('error_email_wrong', true),
				 ),
				 'label'		=> __('register_user_email',true),
		 ));
		 echo $form->input('user_password', array(
				 'type'=>'password',
				 'div'	=> array( 'class' => 'required' ),
				 'error' => array (
						 'notEmpty'	=> __('error_password_empty', true),
						 'pwConfirm'	=> __('error_password_confirm', true),
				 ),
				 'label'		=> __('user_pw',true),
				 ));
		 echo $form->input('password_confirm', array(
				 'type'=>'password',
				 'div' => array( 'class'	=> 'input password required' ),
				 'label'		=> __('user_pw_confirm',true),
				 ));
?>