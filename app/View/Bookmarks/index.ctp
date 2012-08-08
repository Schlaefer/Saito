<?php
	$this->start('headerSubnavLeft');
  echo $this->Html->link(
      '<i class="icon-arrow-left"></i> ' . __('back_to_forum_linkname'),
      '/',
      array( 'class' => 'textlink', 'escape' => FALSE ));
  $this->end();
?>
<div class="box-content">
	<div class="l-box-header box-header">
		<div>
			<div class='c_first_child'></div>
			<div><h1><?php echo $this->TextH->properize($CurrentUser['username']) . ' ' . __('Bookmarks'); ?></h1> </div>
			<div class='c_last_child'></div>
		</div>
	</div>
	<div class="content">
		<?php if ($bookmarks): ?>
			<table class="table table-box-content">
				<thead>
					<tr>
						<th style="width: 15px">
						</th>
						<th style="width: 62%">
							<?php echo __('Subject'); ?>
						</th>
						<th style="width: 15px">
						</th>
						<th>
							<?php echo __('Comment'); ?>
						</th></tr>
				</thead>
				<tbody>
					<?php
						foreach ($bookmarks as $bookmark) {
							$entry_sub = array(
									'Entry'		 => $bookmark['Entry'],
									'Category' => $bookmark['Entry']['Category'],
									'User'		 => $bookmark['Entry']['User'],
							);
						$thread = $this->element('entry/thread_cached', array (
								'entry_sub' => $entry_sub, 'level' => 0 ));

							$table_cells = array(
									$this->Html->link(
											'<i class="icon-trash icon-large"></i>',
											array(
													'controller' => 'bookmarks',
													'action'		 => 'delete',
													$bookmark['Bookmark']['id']),
											array(
													'escape' => false,
													'title'  => __('Delete'),
											)
									),
									"<a name={$bookmark['Entry']['id']}></a>" . $thread,
									$this->Html->link(
											'<i class="icon-edit icon-large"></i>',
											array(
													'controller' => 'bookmarks',
													'action'		 => 'edit',
													$bookmark['Bookmark']['id']),
											array(
													'escape' => false,
													'title'  => __('btn-comment-title'),
											)
									),
									$bookmark['Bookmark']['comment'],
							);
							echo $this->Html->tableCells(
									array($table_cells), array('class' => 'a'), array('class' => 'b')
							);
						}
					?>
				</tbody>
			</table>
		<?php else: ?>
			<?php echo $this->element('generic/no-content-yet', array(
					'message' => __('No bookmarks created yet.'))); ?>
		<?php endif; ?>
	</div>
</div>