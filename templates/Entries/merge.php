<?php

echo $this->Html->tag(
    'h2',
    __('Merge thread {0}', $posting->get('id'))
);

$form[] = $this->Form->create($posting);
$form[] = $this->Form->control(
    'targetId',
    ['label' => __('Merge onto posting with ID:')]
);
$form[] = $this->Form->submit(__('Submit'), ['class' => 'btn btn-primary']);
$form[] = $this->Form->end();
echo implode('', $form);
