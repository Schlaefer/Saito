<?php echo Stopwatch::start('entries/index'); ?>

<?php
  $this->start('headerSubnavLeft');
  echo $this->Html->link(
      '<i class="icon-plus-sign"></i>&nbsp; ' . __('new_entry_linkname'),
      '/entries/add',
      array( 'class' => 'textlink', 'escape' => FALSE ));
  $this->end();
?>

<div id="entry_index" class="entry index">
	<?php echo $this->element('entry/thread_cached_init', array ( 'entries_sub' => $entries, 'level' => 0 )) ; ?>
</div>

<?php
	if (isset($this->passedArgs['jump'])) {
		$this->Html->scriptBlock("$( function() { Thread.scrollTo('{$this->passedArgs['jump']}'); window.history.replaceState('object or string', 'Title', window.location.pathname.replace(/jump:\d+(\/)?/,'')); } );", array('inline' => false));
	}
?>
<?php echo Stopwatch::stop('entries/index');?>