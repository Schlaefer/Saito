<div class="card mb-3">
  <div class="card-header">
      <h2><?= __d('installer', 'populate.title') ?></h2>
  </div>
  <div class="card-body">
    <p class="card-text">
        <?= nl2br(__d('installer', 'populate.explanation')) ?>
    </p>
        <?php
        echo $this->Form->create($admin);
        echo $this->element('users/register-form-core');
        echo $this->Form->submit(__d('installer', 'populate.form.submit'), ['class' => 'btn btn-primary']);
        echo $this->Form->end();
        ?>
  </div>
</div>
