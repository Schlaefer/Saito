<div class="smilies form">
<?php echo $this->Form->create('Smily');?>
	<fieldset>
		<legend><?php echo __('Add Smily'); ?></legend>
	<?php
		echo $this->Form->input('order');
		echo $this->Form->input('icon');
		echo $this->Form->input('image');
		echo $this->Form->input('title');
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit'));?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>

		<li><?php echo $this->Html->link(__('List Smilies'), array('action' => 'index'));?></li>
	</ul>
</div>