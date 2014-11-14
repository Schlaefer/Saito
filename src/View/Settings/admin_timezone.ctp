<div id="settings_timezone" class="settings timezone">
	<div class="row">
		<div class="span6">
			<?php
				echo $this->Form->create(null,
						array( 'inputDefaults' => array(
						),
						'class' => 'well'
				));
				echo $this->Form->select(
						'value',
						$this->TimeH->timezoneOptions(),
						array(
							'label' => __($this->request->data['Setting']['name']),
				));
				echo $this->Form->submit(
						null, array(
						'class' => 'btn-primary',
				));
				echo $this->Form->end();
			?>
		</div>
		<div class="span4">
			<p><?php echo __($this->request->data['Setting']['name'] . '_exp'); ?></p>
		</div>
	</div>
</div>