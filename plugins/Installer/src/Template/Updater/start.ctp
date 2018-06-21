<?php
    $reloadLink = $this->Html->link(
        __d('installer', 'reload'),
        $this->request->getRequestTarget(),
        ['class' => 'btn btn-primary']
    );
    ?>
<div class="jumbotron">
    <h1>
<?= __d('installer', 'update.title') ?></h1>
</div>

<div class="card mb-3" id="a1">
    <div class="card-header">
        <h2><?= __d('installer', 'update.dbOutdated.title') ?></h2>
    </div>
    <div class="card-body">
        <div class="alert alert-danger">
            <?= __d('installer', 'update.dbOutdated.failure') ?>
        </div>
        <p class="card-text">
            <?= __d('installer', 'update.dbOutdated.explanation', ['dbVersion' => $dbVersion, 'saitoVersion' => $saitoVersion]) ?>
        </p>
        <div class="alert alert-warning">
            <?= __d('installer', 'update.dbOutdated.backup') ?>
        </div>
        <?php
        echo $this->Form->create(null);
        echo $this->Form->submit(__d('installer', 'update.dbOutdated.submit'), ['class' => 'btn btn-primary']);
        echo $this->Form->end();
        ?>
    </div>
</div>
