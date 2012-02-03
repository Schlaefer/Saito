<div class="smilies form">
<?php echo $this->Form->create('Smiley');?>
	<fieldset>
		<legend><?php __('Admin Edit Smiley'); ?></legend>
	<?php
		echo $this->Form->input('id');
		echo $this->Form->input('order');
		echo $this->Form->input('icon');
		echo $this->Form->input('image');
		echo $this->Form->input('title');
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit', true));?>
</div>
<div class="actions">
	<h3><?php __('Actions'); ?></h3>
	<ul>

		<li><?php echo $this->Html->link(__('Delete', true), array('action' => 'delete', $this->Form->value('Smiley.id')), null, sprintf(__('Are you sure you want to delete # %s?', true), $this->Form->value('Smiley.id'))); ?></li>
		<li><?php echo $this->Html->link(__('List Smilies', true), array('action' => 'index'));?></li>
		<li><?php echo $this->Html->link(__('List Smiley Codes', true), array('controller' => 'smiley_codes', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Smiley Code', true), array('controller' => 'smiley_codes', 'action' => 'add')); ?> </li>
	</ul>
</div>