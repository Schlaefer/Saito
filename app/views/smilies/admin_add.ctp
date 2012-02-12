<?php $this->Html->addCrumb(__('Smilies', true), '/admin/smilies'); ?>
<?php $this->Html->addCrumb(__('Add Smiley', true), '#'); ?>
<div class="smilies form">
<?php echo $this->Form->create('Smiley');?>
	<fieldset>
		<legend><?php __('Admin Add Smiley'); ?></legend>
	<?php
		echo $this->Form->input('order');
		echo $this->Form->input('icon');
		echo $this->Form->input('image');
		echo $this->Form->input('title');
		echo $this->Form->submit(__('Submit', true), array('class' => 'btn btn-primary'));
	?>
	</fieldset>
<?php echo $this->Form->end();?>
</div>
<div class="actions">
	<h3><?php __('Actions'); ?></h3>
	<ul>

		<li><?php echo $this->Html->link(__('List Smilies', true), array('action' => 'index'));?></li>
		<li><?php echo $this->Html->link(__('List Smiley Codes', true), array('controller' => 'smiley_codes', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Smiley Code', true), array('controller' => 'smiley_codes', 'action' => 'add')); ?> </li>
	</ul>
</div>