<?php if ($level < Configure::read('Saito.Settings.thread_depth_indent')) : ?><ul id="ul_thread_<?php echo $entry_sub['Entry']['id']?>" class="<?php echo  ($level == 0) ? 'thread' : 'reply';?>"><?php endif;?>
<?php echo $this->element('entry/thread_li', array ( 'entry_sub' => $entry_sub, 'level' => $level)); ?>
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
