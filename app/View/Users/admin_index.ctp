<?php $this->Html->addCrumb(__('Users'), '/admin/users'); ?>
<div class="users index">
	<h1><?php echo __('Users');?></h1>
	<?php echo $this->Html->link(__('New User'), array( 'action' => 'add' ), array( 'class' => 'btn' )); ?>
	<hr/>
	<table id="usertable" class="table table-striped">
		<thead>
			<?php
				$tableHeaders = array(
						__('username_marking'),
						__('user_type'),
						__('user_email'),
						__("registered"),
				);
				if (Configure::read('Saito.Settings.block_user_ui')) :
					$tableHeaders[] = __('user.set.lock.t');
				endif;
				echo $this->Html->tableHeaders($tableHeaders);
			?>
		</thead>
		<tbody>
		<?php
			$blockUi = Configure::read('Saito.Settings.block_user_ui');
			foreach ($users as $user) {
				$tableCells = [
						'<strong>'
						. $this->Html->link($user['User']['username'],
								"/users/view/{$user['User']['id']}")
						. '</strong>',
						$this->UserH->type($user['User']['user_type']),
						$this->Html->link(
								$user['User']['user_email'],
								'mailto:' . $user['User']['user_email']
						),
					// output date format sortable by datatable JS plugin
						$this->TimeH->formatTime($user['User']['registered'],
								'%Y-%m-%d %H:%M',
								['wrap' => false])
				];
				if ($blockUi) {
					// without the &nbsp; the JS-sorting with the datatables plugin doesn't work
					$tableCells[] = $this->UserH->banned($user['User']['user_lock']) . '&nbsp;';
				}
				echo $this->Html->tableCells(
						array($tableCells),
						array('class' => 'a'),
						array('class' => 'b')
				);
			}
		?>
		</tbody>
	</table>
</div>
<?php $this->Admin->jqueryTable('#usertable', "[[3, 'desc'], [0, 'asc']]"); ?>
