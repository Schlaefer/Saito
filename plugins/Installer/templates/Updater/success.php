<?php
    $reloadLink = $this->Html->link(
        __d('installer', 'reload'),
        $this->request->getRequestTarget(),
        ['class' => 'btn btn-primary']
    );
    ?>

<div class="card mb-3" id="a1">
    <div class="card-header">
        <h2><?= __d('installer', 'update.finished.title') ?></h2>
    </div>
    <div class="card-body">
        <div class="alert alert-success">
            <?= __d('installer', 'update.finished.success') ?>
        </div>
        <p class="card-text">
            <?= __d('installer', 'update.finished.explanation') ?>
        </p>

        <?php
        echo $this->Html->link(__d('installer', 'update.finished.btn'), '/', ['class' => 'btn btn-primary']);
        ?>
    </div>
</div>
