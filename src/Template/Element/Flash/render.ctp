<?php
$messages = $this->JsData->getMessages();
foreach ($messages['msg'] as $message) {
    $inner = $this->Html->tag('div', $message['message'], ['class' => 'alert']);
    $class = 'flash flash-' . $message['type'];
    echo $this->Html->tag('div', $inner, ['class' => $class]);
}
