<?php
	$paginatorControl = $this->fetch('paginatorControl');
	if (!empty($paginatorControl)) {
		echo $paginatorControl;
		return;
	}

	SDV($options, []);
	$defaults = [
		'format' => '%page%/%pages%'
	];
	$options += $defaults;

	$paginatorControl = '<span class="paginator navbar-item right">';
	if ($this->Paginator->current() > 2) {
		$paginatorControl .= $this->Paginator->first(
			'<i class="fa fa-chevron-left"></i><i class="fa fa-chevron-left"></i>',
			['escape' => false, 'style' => 'padding-right: 1em'],
			null,
			['class' => 'disabled']);
	}

	if ($this->Paginator->hasPrev()) {
		$paginatorControl .= $this->Paginator->prev(
			'<i class="fa fa-chevron-left"></i>',
			['escape' => false],
			null,
			['class' => 'disabled']);
		$paginatorControl .= '&nbsp;';
	}

	echo $this->Paginator->counter(['format' => $options['format']]);

	if ($this->Paginator->hasNext()) {
		$paginatorControl .= '&nbsp;';
		$paginatorControl .= $this->Paginator->next(
			'<i class="fa fa-chevron-right"></i>',
			['escape' => false],
			null,
			['class' => 'disabled']);
	}
	$paginatorControl .= '</span>';

	// caches head-nav paginator for footer-nav
	$paginatorControl = $this->assign('paginatorControl', $paginatorControl);

	echo $paginatorControl;
