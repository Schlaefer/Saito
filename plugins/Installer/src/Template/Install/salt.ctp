<?php
    $reloadLink = $this->Html->link(
        __d('installer', 'reload'),
        $this->request->getRequestTarget(),
        ['class' => 'btn btn-primary']
    );
    ?>
<div class="card mb-3">
  <div class="card-header">
      <h2><?= __d('installer', 'salt.title') ?></h2>
  </div>
  <div class="card-body">
        <?php if (!$secured) : ?>
            <div class="alert alert-danger">
                <?= __d('installer', 'salt.failure') ?>
            </div>
            <p class="card-text">
                <?= __d('installer', 'salt.explanation') ?>
            </p>
            <pre>
            <?= Cake\Utility\Security::randomString(64) ?>
            </pre>
            <pre>
            <?= Cake\Utility\Security::randomString(64) ?>
            </pre>
            <p class="card-text text-white bg-danger p-3">
                <?= __d('installer', 'salt.warning') ?>
            </p>
            <?= $reloadLink ?>
        <?php else : ?>
            <div class="alert alert-success">
                <?= __d('installer', 'salt.success') ?>
            </div>
        <?php endif; ?>
  </div>
</div>
