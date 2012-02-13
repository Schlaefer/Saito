<? if ( $this->request->params['action'] == 'index' && $this->request->params['controller'] == 'entries') : ?>
	<?php echo $this->element('layout/slidetabs__header', array('id' => 'about', 'btn_class' => 'img_info')); ?>
	<? # @td @lo ?>
	<h2> Info </h2>
					<?= number_format($HeaderCounter['entries'], null, null, '.') ?> mal Seemannsgarn in
					<?= number_format($HeaderCounter['threads'], null, null, '.') ?> unglaublichen Geschichten;
					<?= number_format($HeaderCounter['user'], null, null, '.') ?> geheuert,
					<?= $HeaderCounter['user_registered']?> an Deck
	<?php echo $this->element('layout/slidetabs__footer'); ?>
<? endif; ?>