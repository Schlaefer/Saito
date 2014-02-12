<?php
	$this->Html->addCrumb(__('Users'), '/admin/users');
  $this->Html->addCrumb(__('Add User'), '/admin/users/add');
?>
<h1><?php echo __('Add User'); ?></h1>
<?php
	echo $this->Form->create();
	echo $this->element('users/add_form_core');
	echo $this->Form->submit(__('Add User'), ['class'=> 'btn btn-primary']);
	echo $this->Form->end();
?>