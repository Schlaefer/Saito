<div class="card mb-3" id="a4">
  <div class="card-header">
      <h2><?= __d('installer', 'finished.title') ?></h2>
  </div>
  <div class="card-body">
        <p class="card-text">
            <?= nl2br(__d('installer', 'finished.explanation')) ?>
        </p>
        <div class="alert alert-info">
            <?= nl2br(__d('installer', 'finished.debug')) ?>
        </div>
        <?php
        echo $this->Form->postLink(
            __d('installer', 'finished.btn'),
            null,
            [
            'class' => 'btn btn-primary',
            ]
        );
        ?>
  </div>
</div>
