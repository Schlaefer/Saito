<?php $this->Html->addCrumb(__('Smilies'), '/admin/smilies'); ?>
<?php $this->Html->addCrumb(__('Smiley Codes'), '/admin/smiley_codes'); ?>
<?php $this->Html->addCrumb(__('Edit Smiley Code'), '/admin/smiley_codes/edit'); ?>
<h1><?php echo __('Edit Smiley Code'); ?></h1>
<div class="smileyCodes form">
<?php echo $this->Form->create('SmileyCode');?>
	<fieldset>
	<?php
		echo $this->Form->input('id');
		echo $this->Form->input('smiley_id');
		echo $this->Form->input('code');
	?>
	</fieldset>
	<?php
		echo $this->Form->submit(
				__('Submit'), array(
				'class' => 'btn btn-primary',
		));
		echo $this->Form->end();
	?>
</div>