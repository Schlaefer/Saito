<?php

	$out = [
		'isLoggedIn' => $CurrentUser->isLoggedIn()
	];

	if ($CurrentUser->isLoggedIn()) {
		$out += [
			'id'            => (int)$CurrentUser->getId(),
			'username'      => $CurrentUser['username'],
			'last_refresh'  => $this->Api->mysqlTimestampToIso(
				$CurrentUser['last_refresh']
			),
			'threads_order' => $CurrentUser['user_sort_last_answer'] ? 'answer' : 'time'
		];
	}

	echo json_encode($out);
