<?php
	Stopwatch::start('entries/index');

	if ($this->Paginator->current() === 1) {
		$cUrl = $this->Html->url('/', true);
		$seo = '<link rel="canonical" href="' . $cUrl . '"/>';
	} else {
		$seo = '<meta name="robots" content="noindex, follow">';
	}
	$this->append('meta', $seo);

	// set data for immediate shoutbox rendering
	if (isset($shouts)) {
		$this->JsData->set('shouts', $this->Shouts->prepare($shouts));
	}

	$this->start('headerSubnavLeft');

	echo $this->Layout->navbarItem(
		$this->Layout->textWithIcon(h(__('new_entry_linkname')), 'plus'),
		'/entries/add',
		[
			'class' => 'btn-entryAdd',
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
						'id' => 'btn-manuallyMarkAsRead',
						'escape' => false,
						'class' => 'btn-hf-center shp',
						'data-shpid' => 2
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
				['id' => 'btn-category-chooser', 'class' => 'navbar-item right',
					'escape' => false]
		);
		echo $this->element('entry/category-chooser');
	endif;
	$this->end();

	echo $this->Html->div('entry index',
			$this->element('entry/thread_cached_init', ['entries_sub' => $entries]));

	Stopwatch::stop('entries/index');