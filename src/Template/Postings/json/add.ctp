<?php

$out = [
    'id' => $posting->get('id'),
    'type' => 'postings',
    'attributes' => [
        'id' => $posting->get('id'),
        'pid' => $posting->get('pid'),
        'tid' => $posting->get('tid'),
    ],
];

echo json_encode(['data' => $out]);
