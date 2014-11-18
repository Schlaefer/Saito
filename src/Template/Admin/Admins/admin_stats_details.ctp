<?php
	echo $this->Html->script(['lib/flot/jquery.flot.min.js']);

	echo $this->Html->tag('h1', __('admin.stats.detailed'));

	echo $this->Flot->plotDouble(__('admin.stats.epa'), $entries);
	echo $this->Flot->plotDouble(__('admin.stats.ur'), $registrations);
