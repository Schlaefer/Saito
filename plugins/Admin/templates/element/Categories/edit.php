<?php

$form = $this->Form->create($category);
$form .= $this->Form->control('category', ['label' => __('Title')]);
$form .= $this->Form->control('description', ['label' => __('description')]);

$form .= $this->Form->control('accession', [
    'label' => __('accession.read'),
    'options' => $this->Permissions->rolesSelectId(true),
]);

$form .= $this->Form->control('accession_new_thread', [
    'label' => __('accession.new_thread'),
    'options' => $this->Permissions->rolesSelectId(),
]);
$form .= $this->Form->control('accession_new_posting', [
    'label' => __('accession.new_posting'),
    'options' => $this->Permissions->rolesSelectId(),
]);
$form .= $this->Form->control('category_order', ['label' => __('sort.order')]);
$form .= $this->Form->submit(__('Submit'), ['class' => 'btn btn-primary']);
$form .= $this->Form->end();

echo $form;
