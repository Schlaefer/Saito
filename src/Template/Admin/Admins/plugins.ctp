<?php

	use Saito\Event\SaitoEventManager;

	$this->Html->addCrumb(__('Plugins'), '/admin/plugins');

	echo $this->Html->tag('h1', __('Plugins'));
	$items = SaitoEventManager::getInstance()
		->dispatch('Request.Saito.View.Admin.plugins');
	if ($items) {
		foreach ($items as $item) {
			$plugins[] = $this->Html->link(
				$item['title'],
				$item['url']
			);
		}
		echo $this->Html->nestedList($plugins);
	}
