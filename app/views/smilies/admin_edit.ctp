<?php $this->Html->addCrumb(__('Smilies', true), '/admin/smilies'); ?>
<?php $this->Html->addCrumb(__('Edit Smiley',
				true), '#'); ?>
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
		echo $this->Form->submit(__('Submit', true), array('class' => 'btn btn-primary'));
	?>
	</fieldset>
<?php echo $this->Form->end();?>
</div>