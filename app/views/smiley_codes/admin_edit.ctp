<div class="smileyCodes form">
<?php echo $this->Form->create('SmileyCode');?>
	<fieldset>
		<legend><?php __('Admin Edit Smiley Code'); ?></legend>
	<?php
		echo $this->Form->input('id');
		echo $this->Form->input('smiley_id');
		echo $this->Form->input('code');
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit', true));?>
</div>
<div class="actions">
	<h3><?php __('Actions'); ?></h3>
	<ul>

		<li><?php echo $this->Html->link(__('Delete', true), array('action' => 'delete', $this->Form->value('SmileyCode.id')), null, sprintf(__('Are you sure you want to delete # %s?', true), $this->Form->value('SmileyCode.id'))); ?></li>
		<li><?php echo $this->Html->link(__('List Smiley Codes', true), array('action' => 'index'));?></li>
		<li><?php echo $this->Html->link(__('List Smilies', true), array('controller' => 'smilies', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Smiley', true), array('controller' => 'smilies', 'action' => 'add')); ?> </li>
	</ul>
</div>