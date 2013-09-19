<?php
	$out =
			[
				'html' => $this->element(
					'/entry/view_content',
					['entry' => $entry, 'level' => 0]
				)
			];
	$out += $this->JsData->getAppJsMessages();
	echo json_encode($out);
