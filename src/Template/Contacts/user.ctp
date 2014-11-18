<?php
$this->start('headerSubnavLeft');
echo $this->Layout->navbarBack([
    'controller' => 'users',
    'action' => 'view',
    $user->get('id')
]);
$this->end();
?>
<div class="user contact">
    <div class="panel">
        <?= $this->Layout->panelHeading($this->get('title_for_page'),
            ['pageHeading' => true]) ?>
        <div class="panel-content panel-form">
            <?php
            echo $this->Form->create($contact);
            echo $this->element('contacts/contacts-core');
            echo $this->Form->end();
            ?>
        </div>
    </div>
</div>
