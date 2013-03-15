<?php
	$out =
			array(
				'html' => $this->element(
					'/entry/view_content',
					array('entry' => $entry, 'level' => 0)
				)
			);
	$out += $this->JsData->getAppJsMessages();
	echo json_encode($out);
