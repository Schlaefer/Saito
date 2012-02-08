<?php $this->Html->addCrumb(__('Categories', true), '/admin/categories'); ?>
<?php $this->Html->addCrumb(__('Delete Category',
					true), '#'); ?>
<h1>Delete Category <em><?php echo $this->data['Category']['category']; ?></em></h1>
<p>
	You are about to delete the cateogory <?php echo $this->data['Category']['description']; ?>.
	You must decide what should happen with the entries in this category.
</p>

<h2> Move entries and delete category </h2>
<p>
	Deletes the category but moves all entries in the category to another category.
</p>
<?php
	echo $this->Form->create(null, array( 'action' => 'delete', 'admin' => true ));
	echo $this->Form->select('targetCategory', $this->getVar('targetCategory'));
	echo $this->Form->hidden('modeMove', array( 'value' => 1 ));
	echo $this->Form->hidden('modeDelete', array( 'value' => 1 ));
	echo $this->Form->submit(
			__('Move entries and delete category', true),
			array( 'class' => 'btn btn-primary' ));
	echo $this->Form->end();
?>

<h2> Delete entries and delete category </h2>
<p>
	Deletes the category and all entries in it.
</p>
<?php
	echo $this->Html->link(__("Delete entries and delete category", true),
			'#deleteModal',
			array(
			'class' => 'btn btn-danger',
			'data-toggle' => 'modal',
			)
	);
?>

<div id="deleteModal" class="modal hide fade">
  <div class="modal-header">
    <a class="close" data-dismiss="modal">×</a>
    <h3>
			Delete entries and delete category
		</h3>
  </div>
  <div class="modal-body">
		<div class="alert alert-error"> This action can't be undone! Are you sure? </div>
  </div>
  <div class="modal-footer">
		<?php
			echo $this->Form->create(null, array( 'action' => 'delete', 'admin' => true ));
			echo $this->Form->hidden('modeDelete', array( 'value' => 1 ));
			echo $this->Form->submit(
					__("Delete entries and delete category", true),
					array(
					'class' => 'btn btn-danger',
					)
					// sprintf(__('Are you sure you want to delete # %s?', true), $this->data['Category']['id'])
			);
			echo $this->Form->button(
					__('Abort', true),
					array(
							'class' => 'btn',
							'data-dismiss' => 'modal',
					)

					);
			echo $this->Form->end();
		?>
  </div>
</div>
