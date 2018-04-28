<?php

$form = $this->Form->create($category);
$form .= $this->Form->control('category', ['label' => __('Title')]);
$form .= $this->Form->control('description');
$form .= $this->Form->control('accession', [
    'label' => __('accession.read'),
    'options' => [
        0 => __('Anonymous'),
        1 => __('user.type.user'),
        2 => __('user.type.mod'),
        3 => __('user.type.admin')
    ]
]);
$form .= $this->Form->control('accession_new_thread', [
    'label' => __('accession.new_thread'),
    'options' => [
        1 => __('user.type.user'),
        2 => __('user.type.mod'),
        3 => __('user.type.admin')
    ]
]);
$form .= $this->Form->control('accession_new_posting', [
    'label' => __('accession.new_posting'),
    'options' => [
        1 => __('user.type.user'),
        2 => __('user.type.mod'),
        3 => __('user.type.admin')
    ]
]);
$form .= $this->Form->control('category_order');
$form .= $this->Form->submit(__('Submit'), ['class' => 'btn btn-primary']);
$form .= $this->Form->end();

echo $form;
