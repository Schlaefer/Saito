<?php
	/*
	 * Everything you do in here is in worst case done a few hundred times on
	 * the frontpage. Think about (and benchmark) performance before you change it.
	 */

	// because of performance we use dont use $this->Html->link(...):
	// echo $this->EntryH->getFastLink($entry_sub,
	//     array( 'class' => "link_show_thread {$entry_sub['Entry']['id']} span_post_type" ));

	echo $entry_sub['Entry']['subject'];
	echo (empty($entry_sub['Entry']['text']) ? ' n/t' : '');
?>
<?php
// because of performance we use hard coded links instead the cakephp helper:
// echo $this->Html->link($entry_sub['User']['username'], '/users/view/'. $entry_sub['User']['id']);
/* <a href="<?php echo $this->request->webroot; ?>users/view/<?php echo $entry_sub['User']['id']; ?>" ><?php echo $entry_sub['User']['username']; ?></a> */
?>
<span class="thread_line-username">
 –
<?php echo $entry_sub['User']['username']; ?>
</span>
<?php
	if ( $level === 0 ) :
		?>
		<span class='category_acs_<?php echo $entry_sub['Category']['accession']; ?>'
					title="<?php echo $entry_sub['Category']['description']; ?>">
			(<?php echo $entry_sub['Category']['category']; ?>)
		</span>
	<?php endif ?>
<div class="thread_line-post">
			
<?php
	// normal time output
	echo $this->TimeH->formatTime($entry_sub['Entry']['time']);

	// the schlaefer awe-some-o macnemo shipbell time output
	/* <span title="<?php echo $this->TimeH->formatTime($entry_sub['Entry']['time']); ?>"><?php echo $this->TimeH->formatTime($entry_sub['Entry']['time'], 'glasen'); ?>
	  </span> */
?>
			<?php if ( $entry_sub['Entry']['fixed'] ) : ?>
					<i class="icon-pushpin" title="<?php echo __('fixed'); ?>"></i>
				<? endif; ?>
<?php if ( $entry_sub['Entry']['nsfw'] ): ?>
		<span class="sprite-nbs-explicit" title="<?php echo __('entry_nsfw_title') ?>"></span>
	<?php endif; ?>
</div>