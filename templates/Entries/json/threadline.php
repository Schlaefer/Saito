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
    'id' => $entrySub->get('id'),
    'html' => $this->Posting->renderThread(
        $entrySub,
        ['level' => $level]
    ),
];
echo json_encode($out);
