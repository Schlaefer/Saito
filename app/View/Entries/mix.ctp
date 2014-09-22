<?php
	Stopwatch::start('entries/mix');
	$this->start('headerSubnavLeft');

	echo $this->Layout->navbarItem(
		'<i class="fa fa-arrow-left"></i> ' . __('Back'),
		$this->EntryH->getPaginatedIndexPageId($entries[0]['Entry']['tid'],
			$lastAction),
		['escape' => false, 'rel' => 'nofollow']
	);
	$this->end();
?>
	<div class="entry mix" style="position:relative;">
		<?php
			echo $this->Html->link(
					'<div class="btn-strip btn-strip-back">&nbsp;</div>',
					$this->EntryH->getPaginatedIndexPageId(
							$entries[0]['Entry']['tid'],
							$lastAction),
					[
							'escape' => false,
							'rel' => 'nofollow',
					]
			);
		?>
    <div style="margin-left: 25px;">
      <?= $this->EntryH->renderThread($entries[0], $CurrentUser,
        ['renderer' => 'mix']) ?>
    </div>
	</div>
<?php Stopwatch::stop('entries/mix'); ?>