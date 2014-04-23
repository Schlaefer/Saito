<?php
	Stopwatch::start('entries/thread_cached_init');

	SDV($level, 0);
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
		$rendered = $CacheTree->read($entry_sub['Entry']['id']);

		if (!$rendered) {
			// the entry currently viewed (e.g. entries/view)
			SDV($entry, []);
			$rendered = $this->EntryH->threadCached($entry_sub, $CurrentUser, 0, $entry);

			$onFirstPages = !isset($this->request->named['page']) ||
				(int)$this->request->named['page'] < 3;
			if ($onFirstPages && $CacheTree->isCacheUpdatable($entry_sub['Entry'])) {
				$CacheTree->update($entry_sub['Entry']['id'], $rendered);
			}
		}

		/*
		 * for performance reasons we don't use $this->Html->link() in the .threadBox but hardcoded <a>
		 * this scrapes us up to 10 ms on a 40 threads index page
		 */
?>
<div class="threadBox" data-id="<?= $entry_sub['Entry']['id'] ?>">
	<div class="threadBox-body panel">
		<div class="threadBox-tools">
			<?php if ($level === 0) : ?>
					<a href="<?= $this->request->webroot; ?>entries/mix/<?= $entry_sub['Entry']['tid']; ?>" class="btn-threadBox-tools" rel="nofollow">
						<?php echo $cacheThreadBoxTitlei18n['btn-showThreadInMixView']; ?>
					</a>
					<?php if ($CurrentUser->isLoggedIn()): ?>
						&nbsp;
						&nbsp;
						<?php if (isset($toolboxButtonsToDisplay['open'])) : ?>
							<button class="btnLink btn-threadBox-tools js-btn-openAllThreadlines">
								<?php echo $cacheThreadBoxTitlei18n['btn-openThreads']; ?>
							</button>
						<?php endif; ?>
						<?php if (isset($toolboxButtonsToDisplay['close'])) : ?>
							<button class="btnLink btn-threadBox-tools js-btn-closeAllThreadlines">
								<?php echo $cacheThreadBoxTitlei18n['btn-closeThreads']; ?>
							</button>
						<?php endif; ?>
						<?php
							if (isset($toolboxButtonsToDisplay['new'])) :
								if ($this->EntryH->hasNewEntries($entry_sub, $CurrentUser)) {
									$tag = 'button';
								} else {
									$tag = 'span';
								}
						?>
							<<?= $tag; ?>  class="<?php if ($tag === 'button') echo 'btnLink'; ?> btn-threadBox-tools js-btn-showAllNewThreadlines <?php echo ($tag !== 'button') ? 'disabled' : ''; ?>">
									<?= $cacheThreadBoxTitlei18n['btn-showNewThreads'] ?>
							</<?= $tag ?>>
						<?php
							endif;
						?>
					<?php endif; ?>
				<?php endif; ?>
		</div>
			<div style="position: relative;">
				<?php
					$_style = 'visibility: hidden;';
					if ($this->EntryH->hasAnswers($entry_sub) &&
							$this->request->params['controller'] === 'entries' &&
							$this->request->params['action'] === 'index'
					) {
						$_style = '';
					}
				?>
				<button class="btnLink btn-threadCollapse "
								title="<?= $cacheThreadBoxTitlei18n['btn-threadCollapse'] ?>"
								style="<?= $_style ?>">
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