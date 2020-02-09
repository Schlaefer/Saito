<?php
/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 * @var \App\View\AppView $this
 */

$messages = $this->JsData->notifcations()->getAll();
foreach ($messages as $message) {
    $inner = $this->Html->tag('div', $message['message'], ['class' => 'alert']);
    $class = 'flash flash-' . $message['type'];
    echo $this->Html->tag('div', $inner, ['class' => $class]);
}
