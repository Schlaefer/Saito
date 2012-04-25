<div class="fieldset_1">
	<?php
			# @td some labeling and @lo
			echo $this->Form->create('User', array('url' => '/users/login', 'id' => 'login_form' ));
			echo $this->Form->input('username', array( 'label' => __('user_name'), 'value' => '' ) );
			echo $this->Form->input('password', array( 'type' => 'password', 'label' => __('user_pw'), 'value' => ''));
			echo $this->Form->input( 'remember_me', array ( 'label' => array('text' => __('auto_login_marking'), 'style' => 'display: inline;'), 'type' => 'checkbox', 'style' =>'width: auto;' ));
			echo $this->Form->submit( __('login_btn'), array ( 'class' => 'btn_submit' ));
			echo $this->Form->end();
	?>

</div>