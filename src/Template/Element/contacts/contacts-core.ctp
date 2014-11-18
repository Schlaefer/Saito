<?php

echo $this->Form->input('subject', [
    'label' => __('user_contact_subject'),
]);
echo $this->Form->input('text', [
    'style' => 'height: 10em',
    'label' => __('user_contact_message')
]);
echo $this->Form->input('cc', [
    'label' => [
        'text' => __('user_contact_send_carbon_copy'),
        'style' => 'display: inline;',
    ]
]);
echo $this->Form->submit(__('Submit'), [
    'class' => 'btn btn-submit'
]);
