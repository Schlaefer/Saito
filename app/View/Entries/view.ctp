<?php
	Stopwatch::start('view.ctp');

	// subnav left
	$this->start('headerSubnavLeft');
	echo $this->Html->link(
			'<i class="fa fa-arrow-left"></i> ' . __('back_to_forum_linkname'),
			$this->EntryH->getPaginatedIndexPageId(
					$entry['Entry']['tid'],
					$lastAction
			),
			['class' => 'textlink', 'escape' => false, 'rel' => 'nofollow']
	);
	$this->end();

	// description meta tag
	$this->append('meta');
	if (empty($entry['Entry']['text'])) {
		echo $this->Html->tag(
				'meta',
				null,
				['name' => 'description', 'content' => $entry['Entry']['subject']]
		);
	}
	$this->end('meta');
?>
<div class="entry view">
	<div class="panel">
		<?= $this->element('/entry/view_posting', ['entry' => $entry]); ?>
	</div>
	<?=
		$this->element(
				'entry/thread_cached_init',
				['entries_sub' => $tree, 'toolboxButtons' => ['mix' => true]]
		); ?>
</div>
<?php Stopwatch::stop('view.ctp'); ?>
