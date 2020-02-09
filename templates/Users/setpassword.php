<?php
/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 * @var \App\View\AppView $this
 */

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
            __('user.pw.set.t', $user->get('username')),
            ['pageHeading' => true]
        )
?>
    </div>
    <div class="card-body panel-form">
        <?= $this->Form->create(null) ?>
        <div class="form-group">
        <?= $this->Form->control(
            'password',
            [
                'autocomplete' => 'new-password',
                'class' => 'form-control',
                'type' => 'password',
                'label' => __('change_password_new_password'),
                'required' => true,
            ]
        ) ?>
        </div>
        <div class="form-group">
        <?= $this->Form->control(
            'password_confirm',
            [
                'autocomplete' => 'new-password',
                'class' => 'form-control',
                'type' => 'password',
                'label' => __('change_password_new_password_confirm'),
                'required' => true,
            ]
        ) ?>
        </div>
        <div class="form-group">
        <?= $this->Form->submit(
            __('user.pw.set.btn'),
            ['class' => 'btn btn-primary']
        ) ?>
        </div>
        <?= $this->Form->end() ?>
    </div>
</div>
