<?php echo Stopwatch::start('entries/thread_cached_init'); ?>
<?php
	/*
	 * Caching the localized threadbox title tags.
	 * Depending on the number of threads on the page i10n can cost several ms.
	 */
	$cacheThreadBoxTitlei18n = array(
				'btn-showThreadInMixView' => __('btn-showThreadInMixView'),
				'btn-threadCollapse' 			=> __('btn-threadCollapse'),
				'btn-showNewThreads' 			=> __('btn-showNewThreads'),
				'btn-closeThreads' 				=> __('btn-closeThreads'),
				'btn-openThreads' 				=> __('btn-openThreads'),
	);
	$toolboxButtonsToDisplay = [
		'mix'   => 1,
		'open'  => 1,
		'close' => 1,
		'new'   => 1,
	];
	if (isset($toolboxButtons)) {
		$toolboxButtonsToDisplay = $toolboxButtons;
	}
	?>
<?php foreach($entries_sub as $entry_sub) : ?>
<?php
	$use_cached_entry = isset($cachedThreads[$entry_sub['Entry']['id']]);
	if ($use_cached_entry) {
		$out = $CacheTree->read($entry_sub['Entry']['id']);
	} else {
		$out = $this->EntryH->threadCached($entry_sub, $CurrentUser, 0, (isset($entry) ? $entry : array()));
		if (!isset($this->request->named['page']) || (int)$this->request->named['page'] < 3) {
			if ($CacheTree->isCacheUpdatable($entry_sub['Entry'])) {
				$CacheTree->update($entry_sub['Entry']['id'], $out);
			}
		}
	}
?>
<?php
	/*
	 * for performance reasons we don't use $this->Html->link() in the .thread_box but hardcoded <a>
	 * this scrapes us up to 10 ms on a 40 threads index page
	 */
?>
<div class="thread_box" data-id="<?php echo $entry_sub['Entry']['id'];?>">
	<div class='tree_thread box-content'>
		<div class="thread_tools">
			<?php if ($level === 0) : ?>
					<a href="<?= $this->request->webroot; ?>entries/mix/<?= $entry_sub['Entry']['tid']; ?>" class="btn-thread_tools" rel="nofollow">
						<?php echo $cacheThreadBoxTitlei18n['btn-showThreadInMixView']; ?>
					</a>
					<?php if ($CurrentUser->isLoggedIn()): ?>
						&nbsp;
						&nbsp;
						<?php if (isset($toolboxButtonsToDisplay['open'])) : ?>
							<button class="btnLink btn-thread_tools js-btn-openAllThreadlines">
								<?php echo $cacheThreadBoxTitlei18n['btn-openThreads']; ?>
							</button>
						<?php endif; ?>
						<?php if (isset($toolboxButtonsToDisplay['close'])) : ?>
							<button class="btnLink btn-thread_tools js-btn-closeAllThreadlines">
								<?php echo $cacheThreadBoxTitlei18n['btn-closeThreads']; ?>
							</button>
						<?php endif; ?>
						<?php
						if (isset($toolboxButtonsToDisplay['new'])) :
							$tag = 'div';
							if ($this->EntryH->hasNewEntries($entry_sub, $CurrentUser)) :
								// Gecachte Einträge enthalten prinzipiell keine neue Links und brauchen
								// keinen Show All New Inline View Eintrag
								$tag = 'button';
							endif;
						?>
							<<?= $tag; ?><?php if ($tag === 'button') echo 'href="#"'; ?> class="btn-thread_tools js-btn-showAllNewThreadlines <?php echo ($tag === 'div') ? 'disabled' : ''; ?>">
									<?php if ($tag === 'button') echo $cacheThreadBoxTitlei18n['btn-showNewThreads']; ?>
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
			<div class="t_cbe">
				<?= $out; ?>
			</div>
		</div>
	</div>
</div>
<?php endforeach; ?>
<?php echo Stopwatch::stop('entries/thread_cached_init'); ?>