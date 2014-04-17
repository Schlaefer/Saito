<?php
	$this->start('headerSubnavLeft');
	echo $this->Layout->navbarBack([
		'controller' => 'users', 'action' => 'edit', $userId]);
	$this->end();
?>
<div class="panel">
	<?= $this->Layout->panelHeading(__('change_password_link'),
			['pageHeading' => true]) ?>
	<div class="panel-content panel-form">
		<?php
			echo $this->Form->create('User');
			echo $this->Form->input('password_old', [
				'type' => 'password',
				'label' => __('change_password_old_password'),
				'div' => ['class' => 'input password required'],
				'error' => [
					'notEmpty' => __('error_password_empty'),
					'pwCheckOld' => __('error_password_check_old'),
				]]);
			echo $this->Form->input('user_password', [
				'type' => 'password',
				'label' => __('change_password_new_password'),
				'div' => ['class' => 'input required'],
				'error' => [
					'notEmpty' => __('error_password_empty'),
					'pwConfirm' => __('error_password_confirm'),
				]]);
			echo $this->Form->input('password_confirm', [
				'type' => 'password',
				'div' => ['class' => 'input required'],
				'label' => __('change_password_new_password_confirm'),
			]);
			echo $this->Form->submit(__('change_password_btn_submit'),
				['class' => 'btn btn-submit']);
			echo $this->Form->end();
		?>
	</div>
</div>
