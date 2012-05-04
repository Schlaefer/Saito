<h2><?php echo __('RSS Feeds'); ?></h2>
<ul>
	<li>
		<?php echo $this->Html->link(__('RSS Feed') . ' – ' . __('Last entries'), '/entries/feed/feed.rss'); ?>
	</li>
	<li>
		<?php echo $this->Html->link(__('RSS Feed'). ' – ' .__('Last started threads'), '/entries/feed/depth:start/feed.rss'); ?>
	</li>
</ul>