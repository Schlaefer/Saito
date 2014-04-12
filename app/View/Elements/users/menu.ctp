<?php
	$this->start('headerSubnavRight');
	$userMenu = [
		'index' => [
			'url' => '/users/index',
			'title' => __d('page_titles', 'users/index'),
			'icon' => 'users'
		],
		'map' => [
			'url' => '/users/map',
			'title' => __d('page_titles', 'users/map'),
			'icon' => 'map-marker'
		]
	];
	foreach ($userMenu as $m) {
		if (strpos($this->here, $m['url']) !== false) {
			continue;
		}
		$menu[] = $this->Layout->navbarItem(
			$this->Layout->textWithIcon(h($m['title']), $m['icon']),
			$m['url'],
			['position' => 'right', 'escape' => false]);
	}
	echo implode($menu);
	$this->end();
