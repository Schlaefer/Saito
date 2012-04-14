<?php  if ( $this->request->params['action'] == 'index' && $this->request->params['controller'] == 'entries') : ?>
	<?php echo $this->element('layout/slidetabs__header', array('id' => 'about', 'btn_class' => 'img_info')); ?>
	<?php  # @td @lo ?>
	<h2> Info </h2>
					<?php echo  number_format($HeaderCounter['entries'], null, null, '.') ?> mal Seemannsgarn in
					<?php echo  number_format($HeaderCounter['threads'], null, null, '.') ?> unglaublichen Geschichten;
					<?php echo  number_format($HeaderCounter['user'], null, null, '.') ?> geheuert,
					<?php echo  $HeaderCounter['user_registered']?> an Deck
	<?php echo $this->element('layout/slidetabs__footer'); ?>
<?php  endif; ?>