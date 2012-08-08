<div class="box-content">
	<div class="l-box-header box-header">
		<div>
			<div class='c_first_child'></div>
			<div><h1><?php echo $this->TextH->properize($CurrentUser['username']) . ' ' . __('Bookmarks'); ?></h1> </div>
			<div class='c_last_child'></div>
		</div>
	</div>
	<div class="content">
		<table class="">
			<?php
				$table_headers = array(
						'',
						__('Subject'),
						'',
						__('Comment'),
				);
				echo $this->Html->tableHeaders($table_headers);
			?>
			<?php
				foreach ($bookmarks as $bookmark) {
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
							$bookmark['Entry']['subject'],
							$this->Html->link(
									'<i class="icon-edit icon-large"></i>',
									array(
											'controller' => 'bookmarks',
											'action'		 => 'edit',
											$bookmark['Bookmark']['id']),
									array(
											'escape' => false,
											'title'  => __('Edit'),
									)
							),
							$bookmark['Bookmark']['comment'],
					);
					echo $this->Html->tableCells(
							array($table_cells), array('class' => 'a'), array('class' => 'b')
					);
				}
			?>
		</table>
	</div>
</div>