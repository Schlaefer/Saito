<?php
	echo $this->element(
		'shouts/shouts',
		['shouts' => $shouts],
		['cache' => ['key' => 'shouts']]
	);