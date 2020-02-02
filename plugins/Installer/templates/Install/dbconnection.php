<?php
    $reloadLink = $this->Html->link(
        __d('installer', 'reload'),
        $this->request->getRequestTarget(),
        ['class' => 'btn btn-primary']
    );
    ?>

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
