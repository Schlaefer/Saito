<?php $this->Html->addCrumb(__('Categories', true), '/admin/categories'); ?>
<?php $this->Html->addCrumb(__('Add Category', true), '/admin/categories/add'); ?>

<div class="categories form">
	<?php echo $this->Form->create('Category'); ?>
	<fieldset>
		<legend><?php __('Add Category'); ?></legend>
		<?php
//		echo $this->Form->input('category_order');
		echo $this->Form->input('category', array('label' => 'Title') );
		echo $this->Form->input('description');
		echo $this->Form->input('accession', array('options' => array(0,1,2)));
		?>
	</fieldset>
	<?php echo $this->Form->submit(null, array( 'class' => 'btn btn-primary') ); ?>
<?php echo $this->Form->end(); ?>
</div>
<div class="actions">
	<h3><?php __('Actions'); ?></h3>
	<ul>

		<li><?php echo $this->Html->link(__('List Categories',
				true), array( 'action' => 'index' )); ?></li>
	</ul>
</div>