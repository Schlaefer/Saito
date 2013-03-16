<?php
	$out = array(
		'id' => $entry_sub['Entry']['id'],
		'html' => $this->EntryH->threadCached($entry_sub, $CurrentUser, $level)
	);
	echo json_encode($out);
