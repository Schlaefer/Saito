<?php

$this->Breadcrumbs->add(__('Users'), '/admin/users');
$this->Breadcrumbs->add(__('Add User'), '/admin/users/add');

echo $this->Html->tag('h1', __('Add User'));

echo $this->Form->create($user);
echo $this->element('users/register-form-core');
echo $this->Form->submit(__('Add User'), ['class' => 'btn btn-primary']);
echo $this->Form->end();
