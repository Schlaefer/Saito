<?php
	### setup ###
	$last_refresh = $CurrentUser['last_refresh'];
	if (!isset($level)) $level = 0;

	$params = $entryH->generateThreadParams(
						array(
								'level'	=> $level,
								'last_refresh'	=> $last_refresh,
								'entry_time'	=> $entry_sub['Entry']['time'],
								// @td $entry['Entry']['id'] not set in user/view.ctp
								'entry_viewed'	=> (isset($entry['Entry']['id']) && $this->params['action'] == 'view') ? $entry['Entry']['id'] :  null,
								'entry_current'	=> $entry_sub['Entry']['id'],
						)
					);
	extract($params);
	###
?>
<li class="<?php echo $span_post_type ?>">
	<div class="thread_line <?=$entry_sub['Entry']['id']. (($is_new_post) ? " new" : '')?>">
		<a href="#" class="btn_show_thread <?php echo $entry_sub['Entry']['id']; ?>"><span class="img_<?php echo $span_post_type; ?>"></span></a>

	<?php 
# echo $this->element('/entry/thread_line_cached', array( 'cache' => array('key' => $entry_sub['Entry']['id'], 'time' => '+1 hour'), 'entry_sub' => $entry_sub, 'level' => $level )); ?>
	<?php echo $this->element('/entry/thread_line_cached', array('entry_sub' => $entry_sub, 'level' => $level )); ?>
	</div>
</li>