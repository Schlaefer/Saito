<h2>
	<?= __('Merge thread %s', $this->request->data['Entry']['id']) ?>
</h2>
<p>
	<?php
		echo $this->Form->create(null);
		echo $this->Form->input('targetId',
				['label' => __('Merge onto entry with ID:')]);
		echo $this->Form->submit(__('Submit'), ['class' => 'btn btn-primary']);
		echo $this->Form->end();
	?>
</p>