<?php
	$out = [
		'id'            => (int)$entry['Entry']['id'],
		'parent_id'     => (int)$entry['Entry']['pid'],
		'thread_id'     => (int)$entry['Entry']['tid'],
		'subject'       => $entry['Entry']['subject'],
		'is_nt'         => empty($entry['Entry']['text']),
		'time'          => $this->Api->mysqlTimestampToIso(
			$entry['Entry']['time']
		),
		'last_answer'   => $this->Api->mysqlTimestampToIso(
			$entry['Entry']['last_answer']
		),
		'text'          => $entry['Entry']['text'],
		'html'          => $this->Bbcode->parse(
			$entry['Entry']['text'],
			['multimedia' => true]
		),
		'user_id'       => (int)$entry['Entry']['user_id'],
		'user_name'     => $entry['User']['username'],
		'edit_name'     => $entry['Entry']['edited_by'],
		'edit_time'     => $this->Api->mysqlTimestampToIso($entry['Entry']['edited']),
		'category_id'   => (int)$entry['Entry']['category'],
		'category_name' => $entry['Category']['category']
	];

	if ($CurrentUser->isLoggedIn()) {
		$out += [
			'is_locked' => $entry['Entry']['locked'] != false,
		];
	}
	echo json_encode($out);
