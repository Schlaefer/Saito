<?php
$this->start('headerSubnavLeft');
echo $this->Layout->navbarBack();
$this->end();
?>
<div class="user contact">
    <div class="card panel-center">
        <div class="card-header">
            <?= $this->Layout->panelHeading(__('owner_contact_title'), ['pageHeading' => true]) ?>
        </div>
        <div class="card-body panel-content panel-form">
            <?php
            echo $this->Form->create($contact);
            if (!$CurrentUser->isLoggedIn()) {
                echo $this->Form->control(
                    'sender_contact',
                    [
                        'div' => ['class' => 'input required'],
                        'label' => __('user_contact_sender-contact'),
                        'required' => 'required',
                        'type' => 'email'
                    ]
                );
            }
            echo $this->element('contacts/contacts-core');
            echo $this->Form->end();
            ?>
        </div>
    </div>
</div>
