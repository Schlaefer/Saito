<?php $this->Html->addCrumb(__('Categories'), '/admin/categories'); ?>
<?php $this->Html->addCrumb(__('Edit Category'), '#'); ?>
<div class="categories form">
	<h1><?php echo __('Edit Category'); ?></h1>
	<?php echo $this->Form->create('Category'); ?>
	<fieldset>
		<?php
		echo $this->Form->input('id');
		echo $this->Form->input('category_order', ['label' => __('sort.order')]);
		echo $this->Form->input('category');
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
	<?php echo $this->Form->submit(
			__('Submit'),
			array( 'class' => 'btn btn-primary' )); ?>
	<?php echo $this->Form->end(); ?>
</div>