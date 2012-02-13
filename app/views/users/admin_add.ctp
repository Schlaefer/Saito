<?php
	echo $this->Form->create();
	echo $this->element('users/add_form_core');
	echo $this->Form->submit(__('Add User'), array( 'class'=> 'btn-primary'));
	echo $this->Form->end();
?>