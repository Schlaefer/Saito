<?php

$this->start('headerSubnavLeft');
$headerSubnavLeftTitle = $headerSubnavLeftTitle ?? null;
$headerSubnavLeftUrl = $headerSubnavLeftUrl ?? null;
echo $this->Layout->navbarBack($headerSubnavLeftUrl, $headerSubnavLeftTitle);
$this->end();

$attributes = [];
if (isset($posting)) {
    $attributes['data-edit'] = $posting->get('id');
}

echo $this->Html->div('js-answer-wrapper', '', $attributes);
