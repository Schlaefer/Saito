<?php $this->Html->addCrumb(__('Categories', true), '/admin/categories'); ?>
<?php $this->Html->addCrumb(__('Delete Category',
					true), '#'); ?>
<h1>Delete Category <em><?php echo $this->data['Category']['category']; ?></em></h1>
<p>
	You are about to delete the cateogory <?php echo $this->data['Category']['description']; ?>.
	You must decide what should happen with the entries in this category.
</p>

<h2> Delete the category and and move entries </h2>
<p>
	Deletes the category but moves all entries in the category to another category.
</p>
<?php
	echo $this->Form->create(null, array('action' => 'delete', 'admin' => true));
	echo $this->Form->select('targetCategory', $this->getVar('targetCategory'));
	echo $this->Form->hidden('modeMove', array('value' => 1));
	echo $this->Form->hidden('modeDelete', array('value' => 1));
	echo $this->Form->submit(
			__("Delete category and move entries", true),
			array( 'class' => 'btn btn-primary'));
	echo $this->Form->end();
?>

<h2> Delete the category and entries</h2>
<p>
	Deletes the category and entries in it.

<div class="alert alert-error"> This action can't be undone! </div>
</p>
<?php
	echo $this->Html->link(__("Delete category and entries", true), '#deleteModal',
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
			Delete category <em class="categoryTitle"></em>
		</h3>
  </div>
  <div class="modal-body">
		<p>Do you want to delete the entries or move the posting to a different location? </p>
		<?php
			echo $this->Html->link(__('Delete', true),
					array( 'action' => 'delete1', $category['Category']['id'] ),
					array(
					'class' => 'btn btn-danger',
					'data-toggle' => 'modal',
					'data-target' => ''
					),
					sprintf(__('Are you sure you want to delete # %s?', true),
							$category['Category']['id']));
		?>
  </div>
  <div class="modal-footer">
    <a href="#" class="btn btn-primary">Save changes</a>
    <a href="#" class="btn">Abort</a>
  </div>
</div>
