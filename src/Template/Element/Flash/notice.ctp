<?php
$this->JsData->notifications()->add(
    $message,
    [
        'title' => $params['title'] ?? null,
        'type' => 'notice',
    ]
);
