<?php $this->Html->addCrumb(__('Smilies'), '/admin/smilies'); ?>
<?php $this->Html->addCrumb(__('Smiley Codes'), '/admin/smiley_codes'); ?>
<?php $this->Html->addCrumb(__('Add Smiley Code'), '/admin/smiley_codes/add'); ?>
<div class="smileyCodes form">
<?php echo $this->Form->create($smiley);?>
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
