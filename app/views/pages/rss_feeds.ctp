<h2><?php echo __('RSS Feeds'); ?></h2>
<ul>
	<li>
		<?php echo $this->Html->link(__('RSS Feed', true) . ' – ' . __('Last entries', true), '/entries/index.rss'); ?>
	</li>
	<li>
		<?php echo $this->Html->link(__('RSS Feed', true). ' – ' .__('Last started threads', true), '/entries/index.rss/depth:start'); ?>
	</li>
</ul>