<?php

	$out = [];

	foreach ($entries as $entry) {
		$out[] = [
			'id'            => (int)$entry['Entry']['id'],
			'subject'       => $entry['Entry']['subject'],
			'is_nt'         => empty($entry['Entry']['text']),
			'is_pinned'     => (bool)$entry['Entry']['fixed'],
			'time'          => $this->Api->mysqlTimestampToIso(
				$entry['Entry']['time']
			),
			'last_answer'   => $this->Api->mysqlTimestampToIso(
				$entry['Entry']['last_answer']
			),
			'user_id'       => (int)$entry['Entry']['user_id'],
			'user_name'     => $entry['User']['username'],
			'category_id'   => (int)$entry['Entry']['category'],
			'category_name' => $entry['Category']['category']
		];
	}

	echo json_encode($out);
