<?php $this->Html->addCrumb(__('Smilies'), '/admin/smilies'); ?>
<?php $this->Html->addCrumb(__('Add Smiley'), '#'); ?>
<div class="smilies form">
<?php echo $this->Form->create('Smiley');?>
	<fieldset>
		<legend><?php echo __('Admin Add Smiley'); ?></legend>
	<?php
		echo $this->Form->input('order');
		echo $this->Form->input('icon');
		echo $this->Form->input('image');
		echo $this->Form->input('title');
		echo $this->Form->submit(__('Submit'), array('class' => 'btn btn-primary'));
	?>
	</fieldset>
<?php echo $this->Form->end();?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>

		<li><?php echo $this->Html->link(__('List Smilies'), array('action' => 'index'));?></li>
		<li><?php echo $this->Html->link(__('List Smiley Codes'), array('controller' => 'smiley_codes', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Smiley Code'), array('controller' => 'smiley_codes', 'action' => 'add')); ?> </li>
	</ul>
</div>