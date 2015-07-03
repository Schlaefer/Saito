<div id="admins_index" class="admins index">
	<h1>
		<?= __('admin.sysInfo.h') ?>
	</h1>
	<?php
		$si = [
			__('admin.sysInfo.version', $this->Admin->badge(Configure::read('Saito.v'))),
			__('admin.sysInfo.server', $this->Admin->badge(Router::fullBaseUrl())),
			__('admin.sysInfo.baseUrl', $this->Admin->badge($this->request->webroot)),
			__('admin.sysInfo.sitemap', $this->Admin->badge($this->Sitemap->sitemapUrl())),
			__('admin.sysInfo.cce', $this->Admin->badge(Cache::settings('_cake_core_')['engine'], '_cBadge')),
			__('admin.sysInfo.cse', $this->Admin->badge(Cache::settings('default')['engine'], '_cBadge'))
		];
		$si[] = $this->Html->link(
			__('PHP Info'),
			['controller' => 'admins', 'action' => 'phpinfo', 'prefix' => 'admin']
		);

		echo $this->Html->nestedList($si)
	?>
</div>
<hr/>
<?=
	$this->Html->link(__('Empty Caches'),
			['controller' => 'tools', 'action' => 'emptyCaches', 'admin' => true],
			['class' => 'btn'])
?>
