<div class="smilies form">
<?php $this->Html->addCrumb(__('Smilies'), '/admin/smilies'); ?>
<?php $this->Html->addCrumb(__('Add Smiley'), '#'); ?>
<h1><?php echo __('Add Smiley'); ?></h1>
<?php echo $this->Form->create($smiley);?>
	<fieldset>
	<?php
		echo $this->Form->input('icon', ['label' => __('Icon')]);
		echo $this->Form->input('image', ['label' => __('Image')]);
		echo $this->Form->input('title');
        echo $this->Form->input('order', ['label' => __('sort.order')]);
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
