<?php
    $reloadLink = $this->Html->link(
        __d('installer', 'reload'),
        $this->request->getRequestTarget(),
        ['class' => 'btn btn-primary']
    );
    ?>
<div class="jumbotron">
    <h1>
<?= __d('installer', 'title') ?></h1>
</div>

<?php if (strpos(Cake\Core\Configure::read('Saito.language'), 'en') === 0) : ?>
<div class="alert alert-info">
    <?= __d('installer', 'language') ?>
</div>
<?php endif; ?>

<div class="card mb-3" id="a1">
    <div class="card-header">
        <h2><?= __d('installer', 'connection.title') ?></h2>
    </div>
    <div class="card-body">
        <?php if (!$database) : ?>
            <div class="alert alert-danger">
                <?= __d('installer', 'connection.failure') ?>
            </div>
            <p class="card-text">
                <?= __d('installer', 'connection.explanation') ?>
            </p>
            <p class="card-text">
                <?= __d('installer', 'connection.see') ?>
            </p>
            <?= $reloadLink ?>
        <?php else : ?>
            <div class="alert alert-success">
                <?= __d('installer', 'connection.success') ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    window.scrollTo({
        top: document.body.scrollHeight,
        behavior: "smooth"
    });
});
</script>

<?php
if (!$database) {
    return;
}
?>


<div class="card mb-3" id="a2">
  <div class="card-header">
      <h2><?= __d('installer', 'populate.title') ?></h2>
  </div>
  <div class="card-body">
        <?php if (!$tables) : ?>
        <div class="alert alert-danger">
            <?= __d('installer', 'populate.failure') ?>
        </div>
        <p class="card-text">
            <?= nl2br(__d('installer', 'populate.explanation')) ?>
        </p>
            <?php
            echo $this->Form->create(null);
            echo $this->Form->control('username', ['label' => __d('installer', 'populate.form.username')]);
            echo $this->Form->control('password', ['type' => 'text', 'label' => __d('installer', 'populate.form.password')]);
            echo $this->Form->control('user_email', ['type' => 'text', 'label' => __d('installer', 'populate.form.email')]);
            echo $this->Form->submit(__d('installer', 'populate.form.submit'), ['class' => 'btn btn-primary']);
            echo $this->Form->end();
            ?>
        <?php else : ?>
        <div class="alert alert-success">
            <?= __d('installer', 'populate.success') ?>
        </div>
        <?php endif; ?>
  </div>
</div>

<?php

if (!$tables) {
    return;
}
?>



<div class="card mb-3" id="a3">
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

<?php
if (!$secured) {
    return;
}
?>

<div class="card mb-3" id="a4">
  <div class="card-header">
      <h2><?= __d('installer', 'finished.title') ?></h2>
  </div>
  <div class="card-body">
        <p class="card-text">
            <?= __d('installer', 'finished.explanation') ?>
        </p>
        <div class="alert alert-info">
            <?= __d('installer', 'finished.debug') ?>
        </div>
        <?= $reloadLink ?>
  </div>
</div>
