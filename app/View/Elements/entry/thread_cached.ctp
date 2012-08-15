<?php if ($level < Configure::read('Saito.Settings.thread_depth_indent')) : ?><ul id="ul_thread_<?php echo $entry_sub['Entry']['id']?>" class="<?php echo  ($level == 0) ? 'thread' : 'reply';?>"><?php endif;?><?php
		/*
		 * Thread-Line Start
		 */
		### setup ###
		$last_refresh = $CurrentUser['last_refresh'];
		if ( !isset($level) )
			$level = 0;

		$params = $this->EntryH->generateThreadParams(
				array(
						'level' => $level,
						'last_refresh' => $last_refresh,
						'entry_time' => $entry_sub['Entry']['time'],
						// @td $entry['Entry']['id'] not set in user/view.ctp
						'entry_viewed' => (isset($entry['Entry']['id']) && $this->request->params['action'] == 'view') ? $entry['Entry']['id'] : null,
						'entry_current' => $entry_sub['Entry']['id'],
				)
		);
		extract($params);
		###
	?>
	<li class="<?php echo $span_post_type ?>">
		<div class="thread_line <?php echo  $entry_sub['Entry']['id'] . (($is_new_post) ? " new" : '') ?>" style='position: relative;'>
			<div class="thread_line-pre">
				<a href="#" class="btn_show_thread <?php echo $entry_sub['Entry']['id']; ?> span_post_type">
					<i class="icon-<?php echo $span_post_type; ?>"></i>
				</a>
			</div>
			<a href='<?php echo $this->request->webroot; ?>entries/view/<?php echo $entry_sub['Entry']['id']; ?>'
				class='link_show_thread <?php echo $entry_sub['Entry']['id']; ?> span_post_type thread_line-content' >
					<?php
						# echo $this->element('/entry/thread_line_cached', array( 'cache' => array('key' => $entry_sub['Entry']['id'], 'time' => '+1 hour'), 'entry_sub' => $entry_sub, 'level' => $level ));
						echo $this->element('/entry/thread_line_cached',
								array( 'entry_sub' => $entry_sub, 'level' => $level ));
					?>
			</a>
		</div>
	</li>
	<?php
		/*
		 * Thread-Line End
		 */
	?>
	<?php  if (isset($entry_sub['_children'])) : ?>
		<li>
<?php
			foreach ( $entry_sub['_children'] as $child ) :
				echo $this->element('entry/thread_cached', array ( 'entry_sub' => $child, 'level' => $level+1));
			endforeach;
?>
	</li>
	<?php  endif ?>
<?php if ($level < Configure::read('Saito.Settings.thread_depth_indent')) : ?></ul><?php endif;?>