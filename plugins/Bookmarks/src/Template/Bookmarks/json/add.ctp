<?php

$out = [
    'id' => $bookmark->get('id'),
    'type' => 'bookmarks',
    'attributes' => [
        'id' => $bookmark->get('id'),
        'comment' => $bookmark->get('comment'),
        'entry_id' => $bookmark->get('entry_id'),
        'user_id' => $bookmark->get('user_id'),
    ],
];

echo json_encode(['data' => $out]);
