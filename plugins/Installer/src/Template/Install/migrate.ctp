
<div class="card mb-3" id="a2">
  <div class="card-header">
      <h2><?= __d('installer', 'migrate.title') ?></h2>
  </div>
  <div class="card-body">
    <p class="card-text">
        <?= nl2br(__d('installer', 'migrate.explanation')) ?>
    </p>
    <?php
    echo $this->Form->postLink(
        __d('installer', 'migrate.form.startMigration'),
        null,
        [
            'class' => 'btn btn-primary'
        ]
    );
    ?>
  </div>
</div>
