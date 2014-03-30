<div id="admins_index" class="admins index">
	<h1>
		<?= __('admin.sysInfo.h') ?>
	</h1>
	<?=
		$this->Html->nestedList([
				__('admin.sysInfo.version', $this->Admin->badge(Configure::read('Saito.v'))),
				__('admin.sysInfo.server', $this->Admin->badge(Router::fullBaseUrl())),
				__('admin.sysInfo.baseUrl', $this->Admin->badge($this->request->webroot)),
				__('admin.sysInfo.sitemap',
						$this->Admin->badge($this->Sitemap->sitemapUrl()))
		])
	?>
</div>
<hr/>
<?=
	$this->Html->link(__('Empty Caches'),
			['controller' => 'tools', 'action' => 'emptyCaches', 'admin' => true],
			['class' => 'btn'])
?>