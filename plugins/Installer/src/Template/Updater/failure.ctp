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

<div class="card mb-3 text-light bg-danger" id="a1">
    <div class="card-header ">
        <h2><?= __d('installer', 'update.failure.title') ?></h2>
    </div>
    <div class="card-body">
        <p class="card-text">
            <?= $incident ?>
        </p>
        <div class="alert alert-danger">
            <?= __d('installer', 'update.failure.seelog') ?>
        </div>
        <div class="small">
            <?= $code ?>
        </div>
    </div>
</div>
