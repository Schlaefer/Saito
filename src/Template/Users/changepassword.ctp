<?php
$this->start('headerSubnavLeft');
echo $this->Layout->navbarBack(
    ['controller' => 'users', 'action' => 'edit', $userId]
);
$this->end();
?>
<div class="card panel-center">
    <div class="card-header">
        <?=
        $this->Layout->panelHeading(
            __('change_password_link'),
            ['pageHeading' => true]
        )
        ?>
    </div>
    <div class="card-body panel-form">
        <?= $this->Form->create(null) ?>
        <div class="form-group">
        <?= $this->Form->control(
            // helper field for browser's password manager to identify the account
            'username',
            [
                'autocomplete' => 'username',
                'class' => 'form-control',
                'div' => ['class' => 'input'],
                'label' => __('user_name'),
                'type' => 'hidden',
                'value' => $username
            ]
        ) ?>
        </div>
        <div class="form-group">
        <?= $this->Form->control(
            'password_old',
            [
                'autocomplete' => 'current-password',
                'class' => 'form-control',
                'type' => 'password',
                'label' => __('change_password_old_password'),
                'required' => true,
            ]
        ) ?>
        </div>
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
            __('change_password_btn_submit'),
            ['class' => 'btn btn-primary']
        ) ?>
        </div>
        <?= $this->Form->end() ?>
    </div>
</div>
