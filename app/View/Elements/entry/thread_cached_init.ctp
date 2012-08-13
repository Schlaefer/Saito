<?php echo Stopwatch::start('entries/thread_cached_init'); ?>
<?php
	/*
	 * The threadbox title i10n can costs several ms.
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
		$out = $this->element('entry/thread_cached', array ( 'entry_sub' => $entry_sub, 'level' => 0));
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
<div class="thread_box <?php echo $entry_sub['Entry']['id'];?>" data-id="<?php echo $entry_sub['Entry']['id'];?>">
	<?php if ($level == 0 && $this->request->params['action'] == 'index') : ?>
	<div class="thread_tools <?php echo $entry_sub['Entry']['id'];?>">
	<ul>
			<li>
				<a href="<?php echo $this->request->webroot;?>entries/mix/<?php echo $entry_sub["Entry"]['tid']; ?>" id="btn_show_mix_<?php echo $entry_sub['Entry']['tid']; ?>" title="<?php echo $cacheThreadBoxTitlei18n['btn-showThreadInMixView']; ?>">
          <span class="ico-threadTool ico-threadOpenMix"></span>
				</a>
			</li>
			<?php if ($CurrentUser->isLoggedIn()
								// for cached entries this tests if a thread has only the root posting
								&& $entry_sub['Entry']['time'] !== $entry_sub['Entry']['last_answer']) : ?>
				<li>
					<a class="btn-threadCollapse" href="#" title="<?php echo $cacheThreadBoxTitlei18n['btn-threadCollapse']; ?>">
						<span class="ico-threadTool ico-threadCollapse"></span>
					</a>
				</li>
			<?php endif; ?>
			<?php
					if ( $this->request->params['action'] != 'view') :
					### Anzeige der Inline View start ##
						if ( !$use_cached_entry && $CurrentUser->isLoggedIn() ) :
							// Gecachte Einträge enthalten prinzipiell keine neue Links und brauchen
							// keinen Show All New Inline View Eintrag
						?>
						<li>
							<a href="#" id="btn_show_new_threads_<?php echo $entry_sub['Entry']['tid']; ?>" onclick="new Thread('<?php echo $entry_sub['Entry']['tid']; ?>').showNew(); return false;" title="<?php echo $cacheThreadBoxTitlei18n['btn-showNewThreads']; ?>">
                <span class="ico-threadTool ico-threadOpenNew"></span>
							</a>
						</li>
						<?php
						endif;
					endif;
			?>
			<?php if ($CurrentUser->isLoggedIn()) : ?>
				<li>
					<a href="#" id="btn_close_threads_<?php echo $entry_sub['Entry']['tid']; ?>" onclick="new Thread('<?php echo $entry_sub['Entry']['tid']; ?>').closeAll(); return false;" title="<?php echo $cacheThreadBoxTitlei18n['btn-closeThreads']; ?>">
            <span class="ico-threadTool ico-threadCloseInline"></span>
					</a>
				</li>
				<li>
					<a href="#" id="btn_open_threads_<?php echo $entry_sub['Entry']['tid']; ?>" onclick="new Thread('<?php echo $entry_sub['Entry']['tid']; ?>').showAll(); return false;" title="<?php echo $cacheThreadBoxTitlei18n['btn-openThreads']; ?>">
						<span class="ico-threadTool ico-threadOpenInline"></span>
					</a>
				</li>
			<?php endif; ?>
		</ul>
	</div> <!-- thread_tools -->
	<?php endif; ?>
	<div class='tree_thread <?php echo $entry_sub['Entry']['id'];?>'><?php echo $out; ?></div>
</div>
<?php endforeach; ?>
<?php echo Stopwatch::stop('entries/thread_cached_init'); ?>