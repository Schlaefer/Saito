<div class="user contact">
  <div class="panel">
    <?= $this->Layout->panelHeading($this->get('title_for_page'),
      ['pageHeading' => true]) ?>
    <div class="panel-content panel-form">
      <?php
        echo $this->Form->create(false);
        echo $this->element('contacts/contacts-core');
        echo $this->Form->end();
      ?>
    </div>
  </div>
</div>
