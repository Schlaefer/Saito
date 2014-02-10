<div class="panel">
	<?= $this->Layout->panelHeading(__('login_linkname'),
			['pageHeading' => true]) ?>
	<div class="panel-content panel-form">
		<?php
			echo $this->element('users/login_form');
			// set cursor into field username
			echo $this->Js->buffer('$("#tf-login-username").focus();');
			?>

	</div>
</div>