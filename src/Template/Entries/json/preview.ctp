<?php
	$out =
		[
			'html' => $this->element(
				'/entry/view_content',
				['entry' => $posting, 'level' => 0]
			)
		];
	$out += $this->JsData->getAppJsMessages();
	echo json_encode($out);
