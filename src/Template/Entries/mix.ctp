<?php
	use Stopwatch\Lib\Stopwatch;

	Stopwatch::start('entries/mix');
	$this->start('headerSubnavLeft');

	echo $this->Layout->navbarItem(
		'<i class="fa fa-arrow-left"></i> ' . __('Back'),
		$this->Posting->getPaginatedIndexPageId($entries->get('tid'),
			$lastAction),
		['escape' => false, 'rel' => 'nofollow']
	);
	$this->end();
?>
	<div class="entry mix" style="position:relative;">
		<?php
			echo $this->Html->link(
					'<div class="btn-strip btn-strip-back">&nbsp;</div>',
					$this->Posting->getPaginatedIndexPageId(
							$entries->get('tid'),
							$lastAction),
					[
							'escape' => false,
							'rel' => 'nofollow',
					]
			);
		?>
    <div style="margin-left: 25px;">
			<?= $this->Posting->renderThread($entries, ['renderer' => 'mix']) ?>
    </div>
	</div>
<?php Stopwatch::stop('entries/mix'); ?>