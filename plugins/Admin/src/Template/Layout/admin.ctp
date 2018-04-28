<?= $this->Html->docType('html5'); ?>
<html>
<head>
    <title><?= h($titleForLayout) ?></title>
    <?php
    echo $this->Html->charset();
    echo $this->element('layout/script_tags', ['require' => false]);
    echo $this->Html->css([
        'stylesheets/bootstrap.min',
        'Admin.admin.css'
    ]);
    echo $this->Html->script('../dist/bootstrap.min');
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
echo $this->Html->script(
    ['lib/datatables-bootstrap/datatables-bootstrap.js']
);
echo $this->element('layout/debug_footer');
?>
</body>
</html>
