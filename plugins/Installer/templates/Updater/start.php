<?php
    $reloadLink = $this->Html->link(
        __d('installer', 'reload'),
        $this->request->getRequestTarget(),
        ['class' => 'btn btn-primary']
    );
    ?>

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
        echo $this->Html->para('', __d('installer', 'update.form.explanation'));
        echo $this->Form->create($startForm);
        echo $this->Form->control('dbname', ['label' => __d('installer', 'update.form.dbname')]);
        echo $this->Form->control(
            'dbpassword',
            ['label' => __d('installer', 'update.form.dbpassword'), 'type' => 'password']
        );
        if (!empty($startAuthError)) {
            echo $this->Html->para('text-danger', __d('installer', 'update.form.error'));
        }
        echo $this->Form->submit(__d('installer', 'update.dbOutdated.submit'), ['class' => 'btn btn-primary']);
        echo $this->Form->end();
        ?>
    </div>
</div>
