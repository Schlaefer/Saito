<?php $this->Html->addCrumb(__('Smilies'), '/admin/smilies'); ?>
<?php $this->Html->addCrumb(__('Smiley Codes'), '/admin/smiley_codes'); ?>
<?php $this->Html->addCrumb(__('Smiley Codes Edit'), '/admin/smiley_codes/edit'); ?>
<div class="smileyCodes form">
<?php echo $this->Form->create('SmileyCode');?>
	<fieldset>
		<legend><?php echo __('Admin Edit Smiley Code'); ?></legend>
	<?php
		echo $this->Form->input('id');
		echo $this->Form->input('smiley_id');
		echo $this->Form->input('code');
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit'));?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>

		<li><?php echo $this->Html->link(__('Delete'), array('action' => 'delete', $this->Form->value('SmileyCode.id')), null, sprintf(__('Are you sure you want to delete # %s?'), $this->Form->value('SmileyCode.id'))); ?></li>
		<li><?php echo $this->Html->link(__('List Smiley Codes'), array('action' => 'index'));?></li>
		<li><?php echo $this->Html->link(__('List Smilies'), array('controller' => 'smilies', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Smiley'), array('controller' => 'smilies', 'action' => 'add')); ?> </li>
	</ul>
</div>