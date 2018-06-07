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
    <div class="card panel-center">
        <div class="card-header">
            <?= $this->Layout->panelHeading($this->get('titleForPage'), ['pageHeading' => true]) ?>
        </div>
        <div class="card-body panel-form">
            <?php
            echo $this->Form->create($contact);
            echo $this->element('contacts/contacts-core');
            echo $this->Form->end();
            ?>
        </div>
    </div>
</div>
