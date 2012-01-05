<?php
	// because of performance we use hard coded links instead of $html->link(...):
	echo $entryH->getFastLink($entry_sub, array('class'=>"link_show_thread {$entry_sub['Entry']['id']} span_post_type"));
?>
	<?
	# echo $html->link($entry_sub['User']['username'], '/users/view/'. $entry_sub['User']['id']);
	// because of performance we use hard coded links instead the cakephp helpers above:
	?> - 
	<a href="<?php echo $this->webroot; ?>users/view/<?php echo $entry_sub['User']['id']; ?>" ><?php echo $entry_sub['User']['username']; ?></a>,
<?php /* <span title="<?php echo $timeH->formatTime($entry_sub['Entry']['time']); ?>"><?php echo $timeH->formatTime($entry_sub['Entry']['time'], 'glasen'); ?></span> */ ?>
<?php echo $timeH->formatTime($entry_sub['Entry']['time']); ?>
<?

	echo ' ';

	if( $level == 0 ) {
		echo "<span class='category_acs_{$entry_sub['Category']['accession']}'>({$entry_sub['Category']['category']})</span>";
	}
	?>
	<? if ($entry_sub['Entry']['fixed']) : ?>
		<span class="fixed_img" title="<?= __('fixed'); ?>">&nbsp;</span>
	<? endif ; ?>
	<? if ( $entry_sub['Entry']['nsfw'] ): ?>
		<div class="sprite-nbs-explicit" title="<?= __('entry_nsfw_title') ?>"></div>
	<? endif; ?>