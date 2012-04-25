<?php	echo $this->Session->flash('auth'); ?>
<h1><?php echo __('login_linkname'); ?></h1>
	<?php echo  $this->element('users/login_form'); ?>