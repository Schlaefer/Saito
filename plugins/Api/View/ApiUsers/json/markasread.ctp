<?php
	$out = [
		'id'           => $id,
		'last_refresh' => $this->Api->mysqlTimestampToIso($last_refresh)
	];
	echo json_encode($out);
