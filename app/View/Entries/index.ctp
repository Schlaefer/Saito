<?php echo Stopwatch::start('entries/index'); ?>

<?php
  $this->start('headerSubnavLeft');
  echo $this->Html->link(
      '<i class="icon-plus"></i>&nbsp; ' . __('new_entry_linkname'),
      '/entries/add',
      array( 'class' => 'textlink', 'escape' => FALSE ));
  $this->end();

  $this->start('headerSubnavCenter');
		if ($CurrentUser->isLoggedIn()) :
			echo $this->Html->link('&nbsp;<i class="icon-refresh"></i>&nbsp;', '/entries/update',
					array(
							'id'			=> 'btn_manualy_mark_as_read',
							'escape' => false,
							'style'	=> "width: 100px; display: inline-block; height: 20px;",
              'class'         => 'shp shp-bottom',
              'data-title'    => __('Help'),
              'data-content'  => __('btn_manualy_mark_as_read_shp'),
							));
		endif;
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