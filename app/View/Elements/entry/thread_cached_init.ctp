<?php echo Stopwatch::start('entries/thread_cached_init'); ?>
<?php foreach($entries_sub as $entry_sub) : ?>
<?php
	### Ausgabe des Horizontalen Rulers der User Prefs
	/*
	 * currentrly not officially supported in Saito
	$hr = ( isset($hr) && $User['user_forum_hr_ruler'] == 1 ) ? "<hr class='entryline'/>" : '';
	echo $hr;
	 */
	###

	$use_cached_entry = $this->CacheTree->canUseCache($entry_sub['Entry'], $CurrentUser->getSettings());
	if ($use_cached_entry) {
		$out = $this->CacheTree->read($entry_sub['Entry']['id']);
	} else {
		$out = $this->element('entry/thread_cached', array ( 'entry_sub' => $entry_sub, 'level' => 0));

		if ($this->CacheTree->canCacheBeUpdated($entry_sub['Entry'], $CurrentUser->getSettings())) {
			$this->CacheTree->update($entry_sub['Entry']['id'], $out);
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
				<a href="<?php echo $this->request->webroot;?>entries/mix/<?php echo $entry_sub["Entry"]['tid']; ?>" id="btn_show_mix_<?php echo $entry_sub['Entry']['tid']; ?>"><span class="img_mix"></span></a>
			</li>
			<li>
				<a href="#" class=""><span class="btn-threadTool btn-threadCollapse"></span></a>
			</li>
			<?php
					if ( $this->request->params['action'] != 'view') :
					### Anzeige der Inline View start ##
						if ( !$use_cached_entry && $CurrentUser->isLoggedIn() ) :
							// Gecachte Einträge enthalten prinzipiell keine neue Links und brauchen
							// keinen Show All New Inline View Eintrag
						?>
						<li>
							<a href="#" id="btn_show_new_threads_<?php echo $entry_sub['Entry']['tid']; ?>" onclick="new Thread('<?php echo $entry_sub['Entry']['tid']; ?>').showNew(); return false;"><span class="img_open_new"></span></a>
						</li>
						<?php
						endif;
					endif;
			?>
			<?php if ($CurrentUser->isLoggedIn()) : ?>
				<li>
					<a href="#" id="btn_close_threads_<?php echo $entry_sub['Entry']['tid']; ?>" onclick="new Thread('<?php echo $entry_sub['Entry']['tid']; ?>').closeAll(); return false;"><span class="img_inline_close"></span></a>
				</li>
				<li>
					<a href="#" id="btn_open_threads_<?php echo $entry_sub['Entry']['tid']; ?>" onclick="new Thread('<?php echo $entry_sub['Entry']['tid']; ?>').showAll(); return false;"><span class="img_inline_open"></span></a>
				</li>
			<?php endif; ?>
		</ul>
	</div> <!-- thread_tools -->
	<?php endif; ?>
	<div class='tree_thread <?php echo $entry_sub['Entry']['id'];?>'><?php echo $out; ?></div>
</div>
<?php endforeach; ?>
<?php echo Stopwatch::stop('entries/thread_cached_init'); ?>