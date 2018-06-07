<?php
$this->start('headerSubnavLeft');
echo $this->Layout->navbarBack();
$this->end();
?>
<div class="card panel-center">
    <div class="card-header">
        <?php
        echo $this->Layout->panelHeading(
            __('login_linkname'),
            ['pageHeading' => true]
        )
        ?>
    </div>
    <div class="card-body panel-form">
        <?= $this->element('users/login_form') ?>
        <script>
            SaitoApp.callbacks.afterViewInit.push(function () {
                $("#tf-login-username").select();
            });
        </script>
    </div>
</div>
