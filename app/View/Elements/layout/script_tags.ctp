<?php
	SDV($require, true);
	echo $this->Layout->jQueryTag();

	$this->Session->flash();
	$this->Session->flash('email');
	// @td after full js refactoring and moving getAppJs to the page bottom
	// this should go into View/Users/login.ctp again
	$this->Session->flash('auth', array('element' => 'flash/warning'));

	echo $this->Html->scriptBlock($this->JsData->getAppJs($this));

	echo $this->fetch('script-head');

	if ($require) {
		$requireJsScript = 'main';
		if (!Configure::read('debug')) {
			$requireJsScript .= '.min';
		}
		echo $this->RequireJs->scriptTag($requireJsScript);
	}
