<? if ($CurrentUser->isLoggedIn() && $this->params['action'] == 'index' && $this->params['controller'] == 'entries') : ?>
	<?php echo $this->element('layout/slidetabs__header', array('id' => 'donate', 'btn_class' => 'donate_img')); ?>
		<h2>Macnemo unterstützen</h2>
		<p>Macnemo braucht immer eine helfende Hand. <a href="/wiki/index.php/Main/Unterst%c3%bctzen">Das Wie, Was, Wo und Wer findet sich im Wiki.</a></p>
	<?php echo $this->element('layout/slidetabs__footer'); ?>
<? endif; ?>