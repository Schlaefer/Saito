<!DOCTYPE html>
<html>
<head>
    <title><?= h($titleForLayout) ?></title>
    <?php
    echo $this->Html->charset();

    echo $this->Html->script([
        'vendor.bundle.js',
        'exports.bundle.js',
    ]);

    echo $this->Html->css([
        'stylesheets/bootstrap.min',
        'Admin.admin.css',
    ]);
    ?>
</head>
<body>

<?= $this->element('Admin.layout/navbar') ?>

<div class="container">
    <?php
    $breadcrumbs = $this->Breadcrumbs
        ->render(['class' => 'breadcrumb']);
    echo $this->Html->tag('nav', $breadcrumbs);

    echo $this->Flash->render();

    echo $this->fetch('content');
    ?>
</div>
<?php
echo $this->fetch('script');
?>
</body>
</html>
