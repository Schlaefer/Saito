<?php
$this->start('headerSubnavLeft');
echo $this->Layout->navbarBack(
    ['controller' => 'users', 'action' => 'edit', $user->get('id')]
);
$this->end();
?>
<div class="card panel-center">
    <div class="card-header">
        <?=
        $this->Layout->panelHeading(
            __('user.role.set.t', $user->get('username')),
            ['pageHeading' => true]
        )
?>
    </div>
    <div class="card-body panel-form">
        <?= $this->Form->create($user) ?>
        <div class="form-group">
        <?= $this->Form->control(
            'user_type',
            [
                'class' => 'ml-3 mr-1',
                'label' => false,
                'options' => array_map(function ($role) {
                    return ['text' => $this->Permissions->roleAsString($role), 'value' => $role];
                }, $roles),
                'required' => true,
                'type' => 'radio',
            ]
        ) ?>
        </div>
        <div class="form-group">
        <?= $this->Form->submit(
            __('user.role.set.btn'),
            ['class' => 'btn btn-primary']
        ) ?>
        </div>
        <?= $this->Form->end() ?>
    </div>
</div>
