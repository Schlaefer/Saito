<?php

$this->Html->addCrumb(__('Users'), '/admin/users');
$this->Html->addCrumb(__('Add User'), '/admin/users/add');

echo $this->Html->tag('h1', __('Add User'));

echo $this->Form->create($user);
echo $this->element('users/register-form-core');
echo $this->Form->submit(__('Add User'), ['class' => 'btn btn-primary']);
echo $this->Form->end();
