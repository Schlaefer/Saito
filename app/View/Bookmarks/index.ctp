<div class="box-content">
	<div class="l-box-header box-header">
		<div>
			<div class='c_first_child'></div>
			<div><h1><?php echo $this->TextH->properize($CurrentUser['username']) . ' ' . __('Bookmarks'); ?></h1> </div>
			<div class='c_last_child'></div>
		</div>
	</div>
	<div class="content">
		<table>
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
									'Delete',
									array(
									'controller' => 'bookmarks',
									'action'		 => 'delete',
									$bookmark['Bookmark']['id'])
							),
							$bookmark['Entry']['subject'],
							$this->Html->link(
									'Edit',
									array(
									'controller' => 'bookmarks',
									'action'		 => 'edit',
									$bookmark['Bookmark']['id'])
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