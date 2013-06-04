<?php
	Stopwatch::start('entries/index');

	$this->start('headerSubnavLeft');
		echo $this->Html->link(
			'<i class="icon-plus"></i>&nbsp; ' . __('new_entry_linkname'),
			'/entries/add',
			array('id' => 'btn-entryAdd', 'class' => 'textlink', 'escape' => false)
		);
	$this->end();

	$this->start('headerSubnavCenter');
		if ($CurrentUser->isLoggedIn()) :
			echo $this->Html->link(
				'<i class="icon-refresh"></i>',
				'#',
				[
					'id'           => 'btn-manuallyMarkAsRead',
					'escape'       => false,
					'class'        => 'btn-hf-center shp shp-bottom',
					'data-title'   => __('Help'),
					'data-content' => __('btn_manualy_mark_as_read_shp')
				]
			);
		endif;
	$this->end();

	$this->start('headerSubnavRightTop');
		if (isset($categoryChooserIsUsed) && $categoryChooserIsUsed):
			// category-chooser link
			echo $this->Html->link(
				'<i class="icon-tags"></i> '
					. ((isset($categoryChooser[$categoryChooserTitleId])) ? $categoryChooser[$categoryChooserTitleId] : $categoryChooserTitleId)
					. '&nbsp;',
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
	<div id="entry_index" class="entry index">
		<?= $this->element(
			'entry/thread_cached_init',
			['entries_sub' => $entries, 'level' => 0]
		); ?>
	</div>
<?php
	Stopwatch::stop('entries/index');
?>