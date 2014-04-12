<?php
	Stopwatch::start('view.ctp');

	// subnav left
	$this->start('headerSubnavLeft');
	echo $this->Layout->navbarItem(
		'<i class="fa fa-arrow-left"></i> ' . __('back_to_forum_linkname'),
		$this->EntryH->getPaginatedIndexPageId($entry['Entry']['tid'], $lastAction),
		['escape' => false, 'rel' => 'nofollow']
	);
	$this->end();
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
