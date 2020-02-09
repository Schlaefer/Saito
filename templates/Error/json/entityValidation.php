<?php
/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 * @var \App\View\AppView $this
 */

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
