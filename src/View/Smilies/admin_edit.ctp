<?php $this->Html->addCrumb(__('Smilies'), '/admin/smilies'); ?>
<?php $this->Html->addCrumb(__('Edit Smiley'), '#'); ?>
<div class="smilies form">
<h1><?php echo __('Admin Edit Smiley'); ?></h1>
<?php echo $this->Form->create('Smiley');?>
	<fieldset>
	<?php
		echo $this->Form->input('id');
		echo $this->Form->input('order', ['label' => __('sort.order')]);
		echo $this->Form->input('icon');
		echo $this->Form->input('image');
		echo $this->Form->input('title');
		echo $this->Form->submit(__('Submit'), array('class' => 'btn btn-primary'));
	?>
	</fieldset>
<?php echo $this->Form->end();?>
</div>