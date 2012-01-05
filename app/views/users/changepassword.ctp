<h1><?php echo __('change_password_link', true).': '.$this->data['User']['username']; ?></h1>
<div class='fieldset_1'>
	<?php
	 echo $form->create('User');
	 echo $form->input('password_old', array(
			 'type'=>'password',
			 'label'	=> __('change_password_old_password', true),
			 'div' => array( 'class'	=> 'input password required' ),
			 'error' => array (
					 'notEmpty'	=> __('error_password_empty', true),
					 'pwCheckOld'	=> __('error_password_check_old', true),
			 )));
	 echo $form->input('user_password', array(
			 'type'=>'password',
			 'label'	=> __('change_password_new_password', true),
			 'div'		=> array( 'class' => 'required' ),
			 'error' => array (
					 'notEmpty'	=> __('error_password_empty', true),
					 'pwConfirm'	=> __('error_password_confirm', true),
			 )));
	 echo $form->input('password_confirm', array(
			 'type'=>'password',
			 'label'	=> __('change_password_new_password_confirm', true),
			 ));
	 echo $form->submit(__('change_password_btn_submit', true), array( 'class'=> 'btn_submit'));
	 echo $form->end();
	?>
</div>
