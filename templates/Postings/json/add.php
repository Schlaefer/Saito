<?php
/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 * @var \App\View\AppView $this
 */

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
