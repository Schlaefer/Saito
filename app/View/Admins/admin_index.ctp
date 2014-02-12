<div id="admins_index" class="admins index">
	<h1>
		<?= __('admin.sysInfo.h') ?>
	</h1>
	<?php
		$_f = function($val, $i10n) {
			$_data = $this->Html->tag('span', $val, ['class' => 'label label-info']);
			return __($i10n, $_data);
		};
		echo $this->Html->nestedList([
				$_f(Configure::read('Saito.v'), 'admin.sysInfo.version'),
				$_f(Router::fullBaseUrl(), 'admin.sysInfo.server'),
				$_f($this->request->webroot, 'admin.sysInfo.baseUrl'),
		]);
	?>
</div>
<hr/>
<?php
	echo $this->Html->link(__('Empty Caches'),
			array('controller' => 'tools', 'action' => 'emptyCaches', 'admin' => true),
			array(
					'class' => 'btn',
			));
?>