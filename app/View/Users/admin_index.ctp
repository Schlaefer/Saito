<?php $this->Html->addCrumb(__('Users'), '/admin/users'); ?>
<div class="users index">
	<h1><?php echo __('Users');?></h1>
	<?php echo $this->Html->link(__('New User'), array( 'action' => 'add' ), array( 'class' => 'btn' )); ?>
	<hr/>
	<table id="usertable" class="table table-striped table-bordered">
		<thead>
			<?php
				$tableHeaders = array(
						__('username_marking'),
						__('user_type'),
						__('user_email'),
						__("registered"),
				);
				if (Configure::read('Saito.Settings.block_user_ui')) :
					$tableHeaders[] = __('user_lock');
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
								'custom',
								'%Y-%m-%d %H:%M')
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
<?php echo $this->Html->script('lib/datatables/media/js/jquery.dataTables.min.js'); ?>
<?php
	$this->Js->buffer(<<<EOF
$.extend( $.fn.dataTableExt.oStdClasses, {
    "sWrapper": "dataTables_wrapper form-inline"
});
var userTable = $('#usertable').dataTable({
	 "sDom": "<'row'<'span4'l><'span6'f>r>t<'row'<'span4'i><'span6'p>>",
	 "iDisplayLength": 25,
	 "sPaginationType": "bootstrap"
	}).fnSort(
		[[3, 'desc'], [0, 'asc']]
	);
EOF
	);

	$this->Html->block
?>