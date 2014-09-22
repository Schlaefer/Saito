<?php
	$out = [
    'id' => $entry_sub['Entry']['id'],
    'html' => $this->EntryH->renderThread($entry_sub, $CurrentUser,
        ['level' => $level])
	];
	echo json_encode($out);
