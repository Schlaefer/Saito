<div id="settings_timezone" class="settings timezone">
	<div class="row">
		<div class="span6">
			<?php
				echo $this->Form->create(
					$setting,
					['inputDefaults' => [],
						'class' => 'well'
					]
				);
                echo $this->Form->select(
                    'value',
                    $this->TimeH->getTimezoneSelectOptions(),
                    ['label' => __($setting->get('name'))]
                );
				echo $this->Form->submit(
						null,
						[
						'class' => 'btn-primary',
						]
				);
				echo $this->Form->end();
			?>
		</div>
		<div class="span4">
			<p><?php echo __($setting->get('name') . '_exp'); ?></p>
		</div>
	</div>
</div>
