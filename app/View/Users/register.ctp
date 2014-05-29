<?php
  $this->start('headerSubnavLeft');
  echo $this->Layout->navbarBack();
  $this->end();

  $css = ($status === 'view') ? 'panel-form' : '';
?>
<div class="panel">
  <?=
    $this->Layout->panelHeading(__('register_linkname'),
      ['pageHeading' => true]) ?>
  <div class="panel-content staticPage <?= $css ?>">
    <?php
      if ($status === 'view') {
        echo $this->element('users/register-form');
      } elseif ($status === 'fail: email') {
        ?>
        <h1>
          Sending Confirmation Email Failed
        </h1>
        <p>
          Please contact an administrator.
        </p>
      <?php } elseif ($status === 'success') { ?>
        <h1>
          Thanks for Registering
        </h1>
        <p>
          An email with a link was send to you. Please click that link within
          the next 24 hours to confirm your registration.
        </p>
        <p>
          You're not able to login until you have confirmed your registration!
        </p>
      <?php } ?>
  </div>
</div>