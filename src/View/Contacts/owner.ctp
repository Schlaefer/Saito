<?php
  $this->start('headerSubnavLeft');
  echo $this->Layout->navbarBack();
  $this->end();
?>
<div class="user contact">
  <div class="panel">
    <?=
      $this->Layout->panelHeading(__('owner_contact_title'),
        ['pageHeading' => true]) ?>
    <div class="panel-content panel-form">
      <?php
        echo $this->Form->create(false);
        if (!$CurrentUser->isLoggedIn()) {
          echo $this->Form->input(
            'Message.sender_contact',
            [
              'div' => ['class' => 'input required'],
              'label' => __('user_contact_sender-contact'),
              'required' => 'required',
              'type' => 'text'
            ]
          );
        }
        echo $this->element('contacts/contacts-core');
        echo $this->Form->end();
      ?>
    </div>
  </div>
</div>
