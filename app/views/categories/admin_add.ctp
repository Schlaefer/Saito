<div class="categories form">
	<?php echo $this->Form->create('Category'); ?>
	<fieldset>
		<legend><?php __('Admin Add Category'); ?></legend>
		<?php
//		echo $this->Form->input('category_order');
		echo $this->Form->input('category');
		echo $this->Form->input('description');
		echo $this->Form->input('accession', array('options' => array(1,2)));

		echo $this->Form->input('standard_category',
				array( 'type' => 'checkbox', 'checked' => TRUE ));
		?>
	</fieldset>
<?php echo $this->Form->end(__('Submit', true)); ?>
</div>
<div class="actions">
	<h3><?php __('Actions'); ?></h3>
	<ul>

		<li><?php echo $this->Html->link(__('List Categories',
				true), array( 'action' => 'index' )); ?></li>
	</ul>
</div>