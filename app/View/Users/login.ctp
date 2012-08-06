<?php echo $this->Session->flash('auth'); ?>
<div class="box-form">
	<div class="l-box-header box-header">
		<div>
			<div class='c_first_child'></div>
			<div>
				<h1><?php echo __('login_linkname'); ?></h1>
			</div>
			<div class='c_last_child'></div>
		</div>
	</div>
	<div class="content">
		<?php echo $this->element('users/login_form'); ?>
	</div>
</div>