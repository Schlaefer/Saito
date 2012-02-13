<?php $this->Html->addCrumb(__('Categories'), '/admin/categories'); ?>
<?php $this->Html->addCrumb(__('Edit Category'), '#'); ?>
<div class="categories form">
	<?php echo $this->Form->create('Category'); ?>
	<fieldset>
		<legend><?php echo __('Edit Category'); ?></legend>
		<?php
		echo $this->Form->input('id');
		echo $this->Form->input('category_order');
		echo $this->Form->input('category');
		echo $this->Form->input('description');
		echo $this->Form->input('accession',
				array(
				'label' => __('Accession'),
				'options' =>
				array(
						0 => __('Anonymous'),
						1 => __('User'),
						2 => __('Mod'),
				)
				)
		);
		?>
	</fieldset>
	<?php echo $this->Form->submit(null,
			array( 'class' => 'btn btn-primary' )); ?>
	<?php echo $this->Form->end(); ?>
</div>