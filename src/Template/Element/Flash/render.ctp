<?php
$messages = $this->JsData->notifcations()->getAll();
foreach ($messages as $message) {
    $inner = $this->Html->tag('div', $message['message'], ['class' => 'alert']);
    $class = 'flash flash-' . $message['type'];
    echo $this->Html->tag('div', $inner, ['class' => $class]);
}
