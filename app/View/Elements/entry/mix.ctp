<?php
	$span_post_type = $this->EntryH->generateEntryTypeCss(
		$level,
		$this->EntryH->isNewEntry($entry_sub, $CurrentUser),
		$entry_sub['Entry']['id'],
		($this->request->params['action'] === 'view') ? $entry_sub['Entry']['id'] : null
	);
?>
<?php if ($level < Configure::read('Saito.Settings.thread_depth_indent')) : ?>
	<ul class="<?php echo  ($level == 0) ? 'thread' : 'reply';?>">
<?php endif;?>
		<li id="<?= $entry_sub['Entry']['id']?>"
				class="<?php echo $span_post_type ?>" style="margin-bottom: 20px;">
					<div class="a box-content">
						<?php
							echo $this->element('/entry/view_posting', array('entry' => $entry_sub, 'level' => $level, )); #'cache' => array('key' => $entry_sub["Entry"]['id'], 'time' => '+1 day') ));
						?>
					</div>
		</li>
		<?php  if (isset($entry_sub['_children'])) : ?>
			<li>
			<?php
				foreach ( $entry_sub['_children'] as $child ) :
					echo $this->element('entry/mix', array ( 'entry_sub' => $child, 'level' => $level+1, ));
				endforeach;
			?>
			</li>
		<?php  endif ?>
<?php if ($level < Configure::read('Saito.Settings.thread_depth_indent')) : ?>
	</ul>
<?php endif;?>