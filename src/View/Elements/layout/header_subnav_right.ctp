<?php
	echo $this->fetch('headerSubnavRightTop');
	echo $this->assign('headerSubnavRightTop', '');
	echo $this->fetch('headerSubnavRight');

	// if a page has a global paginator we assume it's always shown top right
	if (isset($this->Paginator)) {
		$options = [];
		if ($this->request->params['action'] == 'index') {
			$this->Paginator->options(['url' => null]);
			$options = ['format' => '%page%'];
		}
		echo $this->element('layout/paginator_control', ['options' => $options]);
	}
