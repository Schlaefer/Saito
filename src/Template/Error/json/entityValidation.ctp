<?php

$jsonApiErrors = ['errors' => []];
foreach ($errors as $field => $error) {
    $out = [
        'source' => [
            'pointer' => '/data/attributes/' . $field,
            'field' => '#' . $this->Layout->domId($field),
        ],
        'status' => '400',
        'title' => __d('nondynamic', $field) . ": " . __d('nondynamic', current($error)),
    ];

    $jsonApiErrors['errors'][] = $out;
}

echo json_encode($jsonApiErrors);
