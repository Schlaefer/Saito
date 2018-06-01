<?= $this->Html->docType('html5'); ?>
<html>
<head>
    <title><?= h($titleForLayout) ?></title>
    <?php
    echo $this->Html->charset();

    $this->Flash->render();
    echo $this->Html->script([
        'vendor.bundle.js',
        'exports.bundle.js',
    ]);

    echo $this->Html->css([
        'stylesheets/bootstrap.min',
        'Admin.admin.css'
    ]);
    ?>
</head>
<body>

<?= $this->element('Admin.layout/navbar') ?>

<div class="container">
    <?php
    echo $this->element('Flash/render');

    $breadcrumbs = $this->Breadcrumbs
        ->render(['class' => 'breadcrumb']);
    echo $this->Html->tag('nav', $breadcrumbs);

    echo $this->fetch('content');
    ?>
</div>
<?php
echo $this->fetch('script');
?>
</body>
</html>
