<?php
	$out = [
    'id' => $entry_sub->get('id'),
        'html' => $this->Posting->renderThread(
            $entry_sub,
            ['level' => $level]
        )
	];
	echo json_encode($out);
