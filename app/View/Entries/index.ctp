<?php
	Stopwatch::start('entries/index');

	// set data for immediate shoutbox rendering
	if (isset($shouts)) {
		$this->JsData->set('shouts', $this->Shouts->prepare($shouts));
	}

	$this->start('headerSubnavLeft');
		echo $this->Html->link(
			$this->Layout->textWithIcon(h(__('new_entry_linkname')), 'plus'),
			'/entries/add',
			[
				'class' => 'btn-entryAdd textlink',
				'escape' => false,
				'rel' => 'nofollow'
			]
		);
	$this->end();

	$this->start('headerSubnavCenter');
		if ($CurrentUser->isLoggedIn()) :
			echo $this->Html->link(
				'<i class="fa fa-refresh"></i>',
				'#',
				[
					'id'           => 'btn-manuallyMarkAsRead',
					'escape'       => false,
					'class' => 'btn-hf-center shp',
					'data-shpid' => 2,
				]
			);
		endif;
	$this->end();

	$this->start('headerSubnavRightTop');
		if (isset($categoryChooser)):
			// category-chooser link
			if (isset($categoryChooser[$categoryChooserTitleId])) {
				$_title = $categoryChooser[$categoryChooserTitleId];
			} else {
				$_title = $categoryChooserTitleId;
			}
			echo $this->Html->link(
				$this->Layout->textWithIcon($_title, 'tags'),
				'#',
				[
					'id'     => 'btn-category-chooser',
					'escape' => false
				]
			);
			echo $this->element('entry/category-chooser');
		endif;
	$this->end();
?>
	<div class="entry index">
		<?= $this->element(
			'entry/thread_cached_init',
			['entries_sub' => $entries, 'level' => 0]
		); ?>
	</div>
<?php
	Stopwatch::stop('entries/index');
?>