<!doctype html>
<html>
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <?= $this->Html->css('stylesheets/bootstrap.min') ?>

    <title><?= h(__d('installer', 'title')) ?></title>
  </head>
  <body>
      <div class="container">
        <?= $this->fetch('content') ?>
    </div>
  </body>
</html>
