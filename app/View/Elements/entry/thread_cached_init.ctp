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
	<div class='tree_thread'>
		<div class="thread_tools">
			<?php if ($level == 0 && $this->request->params['action'] == 'index') : ?>
					<a href="<?php echo $this->request->webroot;?>entries/mix/<?php echo $entry_sub['Entry']['tid']; ?>" id="btn_show_mix_<?php echo $entry_sub['Entry']['tid']; ?>" class="btn-thread_tools">
						<?php echo $cacheThreadBoxTitlei18n['btn-showThreadInMixView']; ?>
					</a>
					<?php if ($CurrentUser->isLoggedIn()): ?>
					&nbsp;
					&nbsp;
						<a href="#" class="btn-thread_tools js-btn-openAllThreadlines">
							<?php echo $cacheThreadBoxTitlei18n['btn-openThreads']; ?>
						</a>
						<a href="#" class="btn-thread_tools js-btn-closeAllThreadlines">
							<?php echo $cacheThreadBoxTitlei18n['btn-closeThreads']; ?>
						</a>
						<?php
						if ($this->request->params['action'] != 'view') :
							$tag = 'div';
							if ($this->EntryH->hasNewEntries($entry_sub, $CurrentUser)) :
								// Gecachte Einträge enthalten prinzipiell keine neue Links und brauchen
								// keinen Show All New Inline View Eintrag
								$tag = 'a';
								?>
								<?php
							endif;
						endif;
						?>
						<<?php echo $tag; ?> <?php if ($tag === 'a') echo 'href="#"'; ?> class="btn-thread_tools js-btn-showAllNewThreadlines <?php echo ($tag === 'div') ? 'disabled' : ''; ?>">
									<?php if ($tag === 'a') echo $cacheThreadBoxTitlei18n['btn-showNewThreads']; ?>
						</<?php echo $tag; ?>>

					<?php endif; ?>
				<?php endif; ?>
		</div>
			<div style="position: relative;">
				<?php
					$k = 'visibility: hidden;';
					if (	 $this->EntryH->hasAnswers($entry_sub)
							&& $this->request->params['controller'] === 'entries'
							&& $this->request->params['action'] === 'index'
					) {
						$k = '';
					}
					echo <<<EOF
<a href="#" class="btn-threadCollapse " title="{$cacheThreadBoxTitlei18n['btn-threadCollapse']}" style="{$k}">
<i class="icon-thread-open"></i>
</a>
EOF;
				?>
			<div style="margin-left: 18px;">
				<?php
					echo $out;
				?>
			</div>
		</div>
	</div>
</div>
<?php endforeach; ?>
<?php echo Stopwatch::stop('entries/thread_cached_init'); ?>