<?php echo  Stopwatch::start('view.ctp'); ?>

<?php
  $this->start('headerSubnavLeft');
    echo $this->Html->link(
        '<i class="icon-arrow-left"></i> ' . __('back_to_forum_linkname'),
        $this->EntryH->getPaginatedIndexPageId($entry['Entry']['tid'], $lastAction),
					array(
						'class' => 'textlink',
						'escape' => FALSE,
						'rel' => 'nofollow',
					)
        );
  $this->end();
?>

<?php if (!isset($level)) $level = 0; ?>
<?php
	if ($show_answer) {
		$this->Html->scriptBlock("$(window).load(function() { $('#forum_answer_".$entry["Entry"]['id']."').trigger('click'); });", array('inline' => false ));
	}
?>
<div id="entry_view" class="entry view">
	<div class="a box-content">
				<?php  echo $this->element('/entry/view_posting', array('entry' => $entry, 'level' => $level,)); # 'cache' => array('key' => $entry["Entry"]['id'], 'time' => '+1 day'))); ?>
	</div> <!-- a -->

	<div class="b box-content">
			<div class="thread_tools">
				<a href="<?php echo $this->request->webroot;?>entries/mix/<?php echo $entry['Entry']['tid']; ?>" id="btn_show_mix_<?php echo $entry['Entry']['tid']; ?>" class="btn-thread_tools">
				<?php echo __('btn-showThreadInMixView'); ?>
				</a>
		</div> <!-- thread_tools -->
		<?php echo $this->element(
				'entry/thread_cached_init',
				array (
						'entries_sub' => $tree,
						'level' => 0,
						'thread_toolbox_buttons' => array('mix' => true)
						)
				); ?>
	</div> <!-- b -->
</div>

<?php echo  Stopwatch::stop('view.ctp'); ?>
