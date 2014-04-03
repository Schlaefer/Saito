<?php $this->Html->addCrumb(__('Categories'), '/admin/categories'); ?>
<?php $this->Html->addCrumb(__('Add Category'), '/admin/categories/add'); ?>
<div class="categories form">
<h1><?php echo __('Add Category'); ?></h1>
	<?php echo $this->Form->create('Category'); ?>
	<fieldset>
		<?php
//		echo $this->Form->input('category_order');
		echo $this->Form->input('category', ['label' => __('Title')]);
		echo $this->Form->input('description');
		echo $this->Form->input('accession',
				array(
						'label' => __('Accession'),
				'options' =>
				array(
						0 => __('Anonymous'),
						1 => __('user.type.user'),
						2 => __('user.type.mod')
				)
				)
		);
		?>
	</fieldset>
	<?php echo $this->Form->submit(__('category.create'),
			array( 'class' => 'btn btn-primary' )); ?>
	<?php echo $this->Form->end(); ?>
</div>