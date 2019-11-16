
<?php
$this->start('headerSubnavLeft');
echo $this->Layout->navbarBack(
    ['controller' => 'users', 'action' => 'view', $user->get('id')]
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
                'username',
                ['class' => 'form-control',
                'label' => __('username_marking')]
            ) ?>
        </div>
        <div class="form-group">
            <?= $this->Form->control(
                'user_email',
                ['class' => 'form-control',
                'label' => __('userlist_email')]
            ) ?>
        </div>
        <div class="form-group">
        <?= $this->Form->submit(
            __('user.baseid.set.btn'),
            ['class' => 'btn btn-primary']
        ) ?>
        </div>
        <?= $this->Form->end() ?>
    </div>
</div>
