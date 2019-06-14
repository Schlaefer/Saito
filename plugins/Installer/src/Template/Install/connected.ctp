
<div class="card mb-3" id="a2">
  <div class="card-header">
      <h2><?= __d('installer', 'connected.title') ?></h2>
  </div>
  <div class="card-body">
    <p class="card-text">
        <?= nl2br(__d('installer', 'connected.explanation')) ?>
    </p>
    <?php
    echo $this->Html->link(
        __d('installer', 'connected.button.restart'),
        '/',
        [
            'class' => 'btn btn-primary mr-3',
        ]
    );
    ?>
  </div>
</div>
