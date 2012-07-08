<h2>
	<?php echo __('Merge thread %s', $this->request->data['Entry']['id']); ?>
</h2>
<p>
	<?php echo $this->Form->create(null); ?>
	<?php
		echo $this->Form->input('targetId',
				array(
				'label' => __('Merge onto entry with ID:')
		));
	?>
	<?php
		echo $this->Form->submit(
				null, array(
				'class' => 'btn-primary',
		));
	?>
<?php echo $this->Form->end(); ?>
</p>