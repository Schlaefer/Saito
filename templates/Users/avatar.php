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
echo $this->Layout->navbarBack(['controller' => 'users', 'action' => 'edit', $user->get('id')]);
$this->end();
?>
<div class="card panel-center">
    <div class="card-header">
        <?= $this->Layout->panelHeading($titleForPage, ['pageHeading' => true]) ?>
    </div>
    <div class='card-body panel-form'>
        <div class="row">
            <div class="col-2">
                <?= $this->User->getAvatar($user) ?>
            </div>
            <div class="col-10">
                <?php
                echo $this->Form->create($user, ['class' => 'form-inline', 'type' => 'file']);
                echo $this->Form->control(
                    'avatar',
                    ['label' => false, 'type' => 'file', 'required' => false]
                );
                ?>
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
                            'value' => '1',
                        ]
                    );
                }
                echo $this->Form->end();
                ?>
            </div>
        </div>
    </div>
</div>
