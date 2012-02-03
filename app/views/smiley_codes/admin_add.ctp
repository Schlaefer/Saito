<div class="smileyCodes form">
<?php echo $this->Form->create('SmileyCode');?>
	<fieldset>
		<legend><?php __('Admin Add Smiley Code'); ?></legend>
	<?php
		echo $this->Form->input('smiley_id');
		echo $this->Form->input('code');
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit', true));?>
</div>
<div class="actions">
	<h3><?php __('Actions'); ?></h3>
	<ul>

		<li><?php echo $this->Html->link(__('List Smiley Codes', true), array('action' => 'index'));?></li>
		<li><?php echo $this->Html->link(__('List Smilies', true), array('controller' => 'smilies', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Smiley', true), array('controller' => 'smilies', 'action' => 'add')); ?> </li>
	</ul>
</div>