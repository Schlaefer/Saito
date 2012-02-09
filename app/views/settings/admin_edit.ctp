<div class="row">
	<div class="span6">
			<?php
			echo $form->create(null,
					array( 'inputDefaults' => array(
					),
					'class' => 'well'
			));
			echo $form->input(
					'value',
					array(
					'label' => __($this->data['Setting']['name'], true),
			));
			echo $form->submit(
					null, array(
					'class' => 'btn-primary',
			));
			echo $form->end();
			?>
		</div>
	<div class="span4">
		<p><?php echo __($this->data['Setting']['name'] . '_exp'); ?></p>
	</div>
</div>