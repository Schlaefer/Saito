<?php
$out = [
    'html' => $this->element(
        '/entry/view_content',
        ['entry' => $posting, 'level' => 0]
    )
];
$out += $this->JsData->getMessages();
echo json_encode($out);
