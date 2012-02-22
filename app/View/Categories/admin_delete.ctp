<?php $this->Html->addCrumb(__('Categories'), '/admin/categories'); ?>
<?php $this->Html->addCrumb(__('Delete Category'), '#'); ?>
<h1>Delete Category <em><?php echo $this->request->data['Category']['category']; ?></em></h1>
<p>
	You are about to delete the category <em><?php echo $this->request->data['Category']['category']; ?></em>.
	You must decide what should happen with the entries in this category:
</p>

<div class='row'>
	<div class="span5">
		<div class="well">

			<h3> Move entries and delete category </h3>
			<p>
				Deletes the category but moves all entries into another category:
			</p>
			<?php
				echo $this->Form->create(null,
						array(
						'url' => array( 'action' => 'delete', 'admin' => true ),
						'class' => '' )
				);

				echo $this->Form->label('targetCategory', 'Move to Cateogry:');
				echo $this->Form->select('targetCategory', $this->getVar('targetCategory'));
				echo $this->Form->hidden('modeMove', array( 'value' => 1 ));
				echo $this->Form->hidden('modeDelete', array( 'value' => 1 ));
				echo $this->Form->submit(
						__('Move entries and delete category'),
						array( 'class' => 'btn btn-primary' ));
				echo $this->Form->end();
			?>
		</div>
	</div>
	<div class="span5">
		<div class="well">

			<h3> Delete entries and delete category </h3>
			<p>
				Deletes the category and all entries in it.
			</p>
			<?php
				echo $this->Html->link(__("Delete entries and delete category"),
						'#deleteModal',
						array(
						'class' => 'btn btn-danger',
						'data-toggle' => 'modal',
						)
				);
			?>
		</div>
	</div>
</div>
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
					__("Delete entries and delete category"),
					array(
					'class' => 'btn btn-danger',
					)
					// sprintf(__('Are you sure you want to delete # %s?'), $this->request->data['Category']['id'])
			);
			echo $this->Form->button(
					__('Abort'),
					array(
					'class' => 'btn',
					'data-dismiss' => 'modal',
					)
			);
			echo $this->Form->end();
		?>
	</div>
</div>
