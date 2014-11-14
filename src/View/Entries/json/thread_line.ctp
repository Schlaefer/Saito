<?php
	$out = [
    'id' => $entry_sub['Entry']['id'],
    'html' => $this->EntryH->renderThread($entry_sub, ['level' => $level])
	];
	echo json_encode($out);
