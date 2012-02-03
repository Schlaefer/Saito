<div class="fieldset_1">
	<?php	echo $session->flash('auth'); ?>
	<?php
			# @td some labeling and @lo
			echo $form->create('User', array('url' => '/users/login', 'id' => 'login_form' ));
			echo $form->input('username', array( 'label' => __('user_name', true), 'value' => '' ) );
			echo $form->input('password', array( 'type' => 'password', 'label' => __('user_pw', true), 'value' => ''));
			echo $form->input( 'remember_me', array ( 'label' => array('text' => __('auto_login_marking', true), 'style' => 'display: inline;'), 'type' => 'checkbox', 'style' =>'width: auto;' ));
			echo $form->submit( __('login_btn', true), array ( 'class' => 'btn_submit' ));
			echo $form->end();
	?>

</div>