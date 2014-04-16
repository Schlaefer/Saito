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
			$(function() {
				var focus = function() {
					if ($("#content").css('visibility') === 'hidden') {
						window.setTimeout(focus, 300);
						return;
					}
					$("#tf-login-username").select();
				};
				focus();
			});
		</script>
	</div>
</div>