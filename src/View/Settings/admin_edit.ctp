<?php
	$this->Html->addCrumb(__('Settings'), '/admin/settings');
	$this->Html->addCrumb(__d('nondynamic', $this->request->data['Setting']['name']), '#');
?>
<h1><?php echo __d('nondynamic', $this->request->data['Setting']['name']); ?></h1>
<div class="row">
	<div class="span6">
		<?php
			echo $this->Form->create(null,
					array('inputDefaults' => array(
					),
					'class' => 'well'
			));
			echo $this->Form->input(
					'value',
					array(
					'label' => __d('nondynamic', $this->request->data['Setting']['name']),
			));
			echo $this->Form->submit(
					__('Submit'), array(
					'class' => 'btn-primary',
			));
			echo $this->Form->end();
		?>
	</div>
	<div class="span4">
		<p><?php echo __d('nondynamic', $this->request->data['Setting']['name'] . '_exp'); ?></p>
	</div>
</div>