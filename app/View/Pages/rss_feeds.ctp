<div class="panel">
	<?= $this->Layout->panelHeading(__('RSS Feeds'), ['pageHeading' => true]) ?>
	<div class="panel-content staticPage">
		<?= $this->Html->nestedList([
			$this->Html->link(__('RSS Feed') . ' – ' . __('Last entries'), '/entries/feed/feed.rss'),
			$this->Html->link(__('RSS Feed'). ' – ' .__('Last started threads'), '/entries/feed/depth:start/feed.rss')
		]); ?>
	</div>
</div>
