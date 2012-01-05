<div id="#settings_edit" class="settings edit">
	<?php
		echo $form->create();
		echo $form->input(
						'value',
						array (
								'label'	=> __($this->data['Setting']['name'], true),
						));
		echo $form->submit(
						null,
						array (
								'class'	=> 'btn_submit',
						));
		echo $form->end();
	?>
	<p><?php echo __($this->data['Setting']['name'].'_exp'); ?></p>
</div>