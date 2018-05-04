<?php
$this->start('headerSubnavLeft');
echo $this->Layout->navbarBack(['controller' => 'users', 'action' => 'edit', $user->get('id')]);
$this->end();
?>
<div class="panel">
    <?= $this->Layout->panelHeading($titleForPage, ['pageHeading' => true]) ?>
    <div class='panel-content panel-form'>
        <?php
        echo $this->User->getAvatar($user);
        echo $this->Form->create($user, ['type' => 'file']);
        echo $this->Form->control(
            'avatar',
            ['type' => 'file', 'required' => false]
        );
        ?>
        <div class="panel-footer panel-form">
            <?php
            echo $this->Form->button(
                __('gn.btn.save.t'),
                ['class' => 'btn btn-primary']
            );
            $avatar = $user->get('avatar');
            if (!empty($avatar)) {
                echo $this->Form->button(
                    __('gn.btn.delete.t'),
                    [
                        'class' => 'btn btn-link',
                        'name' => 'avatarDelete',
                        'value' => '1'
                    ]
                );
            }
            echo $this->Form->end();
            ?>
        </div>
    </div>
</div>
