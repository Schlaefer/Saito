<div class="smileyCodes form">
<?php echo $this->Form->create('SmileyCode');?>
	<fieldset>
		<legend><?php echo __('Add Smiley Code'); ?></legend>
	<?php
		echo $this->Form->input('smiley_id');
		echo $this->Form->input('code');
		echo $this->Form->submit(__('Submit'), ['class' => 'btn btn-primary'])
	?>
	</fieldset>
<?php echo $this->Form->end();?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>

		<li><?php echo $this->Html->link(__('List Smiley Codes'), array('action' => 'index'));?></li>
		<li><?php echo $this->Html->link(__('List Smilies'), array('controller' => 'smilies', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Smiley'), array('controller' => 'smilies', 'action' => 'add')); ?> </li>
	</ul>
</div>