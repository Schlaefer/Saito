<?php
/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 * @var \App\View\AppView $this
 */

$this->JsData->notifications()->add(
    $message,
    [
        'title' => $params['title'] ?? null,
        'type' => 'warning',
    ]
);
