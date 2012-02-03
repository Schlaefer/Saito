<?php
	### setup ###
	$last_refresh = $CurrentUser['last_refresh'];

	$params = $entryH->generateThreadParams(
						array(
								'level'	=> $level,
								'last_refresh'	=> $last_refresh,
								'entry_time'	=> $entry_sub['Entry']['time'],
								'entry_viewed'	=> ($this->params['action'] == 'view') ? $entry['Entry']['id'] :  null,
								'entry_current'	=> $entry_sub['Entry']['id'],
						)
					);
	extract($params);
	###
?>
<a name="<?php echo $entry_sub['Entry']['id'] ;?>"></a>
<?php if ($level < Configure::read('Saito.Settings.thread_depth_indent')) : ?>
	<ul class="<?= ($level == 0) ? 'thread' : 'reply';?>">
<?php endif;?>
		<li class="<?php echo $span_post_type ?>" style="margin-bottom: 20px;">
					<div class="a">
						<?
							echo $this->element('/entry/view_posting', array('entry' => $entry_sub, 'level' => $level, )); #'cache' => array('key' => $entry_sub["Entry"]['id'], 'time' => '+1 day') ));
						?>
					</div>
		</li>
		<? if (isset($entry_sub['_children'])) : ?>
			<li>
			<?
				foreach ( $entry_sub['_children'] as $child ) :
					echo $this->element('entry/mix', array ( 'entry_sub' => $child, 'level' => $level+1, ));
				endforeach;
			?>
			</li>
		<? endif ?>
<?php if ($level < Configure::read('Saito.Settings.thread_depth_indent')) : ?>
	</ul>
<?php endif;?>