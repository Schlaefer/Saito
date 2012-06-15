<?php $this->Html->addCrumb(__('Delete User'), '#'); ?>
<h1>Delete User <em><?php echo $user['User']['username']; ?></em></h1>

<div class='row'>
	<div class="span10">
		<div class="well">
      <p>
        You are about to delete the user <em><?php echo $user['User']['username']; ?></em>. 
      </p>
      <ul>
        <li>
          His/her uploads will be deleted.
        </li>
        <li>
          His/her profil data will be deleted.
        </li>
        <li>
          His/her entries will remain with an unknown user as origin.
        </li>
      </ul>
			<?php
				echo $this->Html->link(__("Delete user %s", $user['User']['username']),
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
			Delete user <?php echo $user['User']['username']; ?>
		</h3>
	</div>
	<div class="modal-body">
		<div class="alert alert-error"> This action can't be undone! Are you sure? </div>
	</div>
	<div class="modal-footer">
		<?php
			echo $this->Form->create();
			echo $this->Form->hidden('modeDelete', array( 'value' => 1 ));
			echo $this->Form->submit(
					__("Make It So"),
					array(
					'class' => 'btn btn-danger',
					)
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
