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
        <?= $this->Form->create($user, ['type' => 'file']) ?>
            <div class="d-flex flex-row">
                <div class="d-flex flex-column mr-4" style="text-align:center">
                    <?= $this->User->getAvatar($user) ?>
                    <?php
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
                    ?>
                </div>
                <div class="form-group form-inline mb">
                        <?= $this->Form->control( 'avatar', ['label' => false, 'type' => 'file', 'required' => false]) ?>
                    <?php
                    echo $this->Form->button(
                        __('gn.btn.save.t'),
                        ['class' => 'btn btn-primary']
                    );
                    ?>
                </div>
            </div>
        <?= $this->Form->end() ?>
    </div>
</div>
