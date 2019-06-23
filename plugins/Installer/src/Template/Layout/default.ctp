<!doctype html>
<html>
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <?= $this->Html->css('stylesheets/bootstrap.min') ?>

    <title><?= h($titleForLayout) ?></title>
  </head>
  <body>
      <div class="container">
        <div class="jumbotron">
            <h1><?= h($titleForLayout) ?></h1>
        </div>
        <?php if (strpos(Cake\Core\Configure::read('Saito.language'), 'en') === 0) : ?>
        <div class="alert alert-info">
            <?= __d('installer', 'language') ?>
        </div>
        <?php endif; ?>

        <?= $this->fetch('content') ?>
    </div>
  </body>
</html>
