<?php
	/*
	 * Everything you do in here is in worst case done a few hundred times on
	 * the frontpage. Think about (and benchmark) performance before you change it.
	 */

	// because of performance we use dont use $this->Html->link(...):
	echo $this->EntryH->getFastLink($entry_sub,
			array( 'class' => "link_show_thread {$entry_sub['Entry']['id']} span_post_type" ));
?>
<?php
// because of performance we use hard coded links instead the cakephp helper:
// echo $this->Html->link($entry_sub['User']['username'], '/users/view/'. $entry_sub['User']['id']);
?> - 
<a href="<?php echo $this->request->webroot; ?>users/view/<?php echo $entry_sub['User']['id']; ?>" ><?php echo $entry_sub['User']['username']; ?></a>,
<?php
	// normal time output
	echo $this->TimeH->formatTime($entry_sub['Entry']['time']);

	// the schlaefer awe-some-o macnemo shipbell time output
	/* <span title="<?php echo $this->TimeH->formatTime($entry_sub['Entry']['time']); ?>"><?php echo $this->TimeH->formatTime($entry_sub['Entry']['time'], 'glasen'); ?>
	  </span> */
?>
<?php
	if ( $level === 0 ) :
		?>
		<span class='category_acs_<?php echo $entry_sub['Category']['accession']; ?>'
					title="<?php echo $entry_sub['Category']['description']; ?>">
			(<?php echo $entry_sub['Category']['category']; ?>)
		</span>
	<?php endif ?>
<?php if ( $entry_sub['Entry']['fixed'] ) : ?>
		<span class="fixed_img" title="<?php echo __('fixed'); ?>">&nbsp;</span>
	<? endif; ?>
<?php if ( $entry_sub['Entry']['nsfw'] ): ?>
		<div class="sprite-nbs-explicit" title="<?php echo __('entry_nsfw_title') ?>"></div>
	<?php endif; ?>