<?php
	echo $this->Html->script(['lib/flot/jquery.flot.min.js']);

	echo $this->Html->tag('h1', __('admin.stats.yearly'));

	echo $this->Flot->plot(__('admin.stats.epa'), $postingsPA);
	echo $this->Flot->plot(__('admin.stats.epac'), $categoryPostingsPA);

	echo $this->Flot->plot(__('admin.stats.au'), $activeUserPA);
	echo $this->Html->para(null, __('admin.stats.audisc'));

	echo $this->Flot->plot(__('admin.stats.apu'), $averagePostingsPerUserPA);
	echo $this->Html->para(null, __('admin.stats.audisc'));

	echo $this->Flot->plot(__('admin.stats.ur'), $registrationsPA);
