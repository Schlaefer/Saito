<?php
  Stopwatch::start('entries/thread_cached_init');

  SDV($level, 0);
	SDV($allowThreadCollapse, false);
  /*
   * Caching the localized threadbox title tags.
   * Depending on the number of threads on the page i10n can cost several ms.
   */
  $cacheThreadBoxTitlei18n = array(
    'btn-showThreadInMixView' => __('btn-showThreadInMixView'),
    'btn-threadCollapse' => __('btn-threadCollapse'),
    'btn-showNewThreads' => __('btn-showNewThreads'),
    'btn-closeThreads' => __('btn-closeThreads'),
    'btn-openThreads' => __('btn-openThreads'),
  );
  $toolboxButtonsToDisplay = [
    'mix' => 1,
    'open' => 1,
    'close' => 1,
    'new' => 1,
  ];
  if (isset($toolboxButtons)) {
    $toolboxButtonsToDisplay = $toolboxButtons;
  }

  foreach ($entries_sub as $entry_sub) :
    // the entry currently viewed (e.g. entries/view)
    $currentEntry = null;
    if (isset($entry)) {
      $currentEntry = $entry['Entry']['id'];
    }
		$rendered = $this->EntryH->renderThread($entry_sub,
			['currentEntry' => $currentEntry]);
    $css = ($entry_sub->getThread()->get('root')->isIgnored()) ? 'ignored' : '';
?>
<div class="threadBox <?= $css ?>" data-id="<?= $entry_sub->get('id') ?>">
	<div class="threadBox-body panel">
		<div class="threadBox-tools">
			<?php if ($level === 0) : ?>
					<a href="<?= $this->request->webroot; ?>entries/mix/<?= $entry_sub->get('tid'); ?>" class="btn-threadBox-tools" rel="nofollow">
						<?= $cacheThreadBoxTitlei18n['btn-showThreadInMixView']; ?>
					</a>
					<?php if ($CurrentUser->isLoggedIn()): ?>
						&nbsp;
						&nbsp;
						<?php if (isset($toolboxButtonsToDisplay['open'])) : ?>
							<button class="btnLink btn-threadBox-tools js-btn-openAllThreadlines">
								<?= $cacheThreadBoxTitlei18n['btn-openThreads']; ?>
							</button>
						<?php endif; ?>
						<?php if (isset($toolboxButtonsToDisplay['close'])) : ?>
							<button class="btnLink btn-threadBox-tools js-btn-closeAllThreadlines">
								<?= $cacheThreadBoxTitlei18n['btn-closeThreads']; ?>
							</button>
						<?php endif; ?>
						<?php
							if (isset($toolboxButtonsToDisplay['new'])) :
								if ($entry_sub->hasNewAnswers()) {
									$tag = 'button';
								} else {
									$tag = 'span';
								}
						?>
							<<?= $tag; ?>  class="<?php if ($tag === 'button') echo 'btnLink'; ?> btn-threadBox-tools js-btn-showAllNewThreadlines <?php echo ($tag !== 'button') ? 'disabled' : ''; ?>">
									<?= $cacheThreadBoxTitlei18n['btn-showNewThreads'] ?>
							</<?= $tag ?>>
						<?php endif; // button 'new' ?>
					<?php endif; // logged in ?>
				<?php endif; // level = 0?>
		</div>
			<div style="position: relative;">
				<?php
					$style = '';
					if (!$allowThreadCollapse || !$entry_sub->hasAnswers()) {
						$style = 'visibility: hidden;';
					}
				?>
				<button class="btnLink btn-threadCollapse "
								title="<?= $cacheThreadBoxTitlei18n['btn-threadCollapse'] ?>"
								style="<?= $style ?>">
					<i class="fa fa-thread-open"></i>
				</button>
			<div class="threadBox-threadTree">
				<?= $rendered; ?>
			</div>
		</div>
	</div>
</div>
<?php endforeach; ?>
<?php Stopwatch::stop('entries/thread_cached_init'); ?>