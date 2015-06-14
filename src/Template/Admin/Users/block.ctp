<?php
$this->Html->addCrumb(__('Users'), '/admin/users');
$this->Html->addCrumb(__('user.block.history'), '/admin/users/block');
echo $this->Html->tag('h1', __('user.block.history'));

echo $this->element(
    'users/block-report',
    ['mode' => 'full', 'UserBlock' => $UserBlock]
);

$this->Admin->jqueryTable('#blocklist', "[[1, 'desc'], [3, 'desc']]");
