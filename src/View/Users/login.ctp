<?php
	$this->start('headerSubnavLeft');
	echo $this->Layout->navbarBack();
	$this->end();
?>
<div class="panel">
	<?= $this->Layout->panelHeading(__('login_linkname'),
			['pageHeading' => true]) ?>
	<div class="panel-content panel-form">
		<?= $this->element('users/login_form') ?>
		<script>
			SaitoApp.callbacks.afterViewInit.push(function() {
				$("#tf-login-username").select();
			});
		</script>
	</div>
</div>