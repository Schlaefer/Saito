<?php

	$out = [
		'isLoggedIn' => $CurrentUser->isLoggedIn()
	];

	if ($CurrentUser->isLoggedIn()) {
		$out += [
			'id'            => (int)$CurrentUser->getId(),
			'username'      => $CurrentUser->get('username'),
			'last_refresh'  => $this->Api->mysqlTimestampToIso(
				$CurrentUser->get('last_refresh')
			),
			'threads_order' => $CurrentUser->get('user_sort_last_answer') ? 'answer' : 'time'
		];
	}

	echo json_encode($out);
