<div class="categories form">
<?php echo $this->Form->create('Category');?>
	<fieldset>
		<legend><?php __('Add Category'); ?></legend>
	<?php
		echo $this->Form->input('category_order');
		echo $this->Form->input('category');
		echo $this->Form->input('description');
		echo $this->Form->input('accession');
		echo $this->Form->input('standard_category');
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit', true));?>
</div>
<div class="actions">
	<h3><?php __('Actions'); ?></h3>
	<ul>

		<li><?php echo $this->Html->link(__('List Categories', true), array('action' => 'index'));?></li>
		<li><?php echo $this->Html->link(__('List Entries', true), array('controller' => 'entries', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Entry', true), array('controller' => 'entries', 'action' => 'add')); ?> </li>
	</ul>
</div>