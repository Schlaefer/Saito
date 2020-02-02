<?php
$this->start('headerSubnavLeft');
echo $this->Layout->navbarBack();
$this->end();

$css = ($status === 'view') ? 'panel-form' : '';
?>
<div class="panel">
    <?=
    $this->Layout->panelHeading(
        __('register_linkname'),
        ['pageHeading' => true]
    ) ?>
    <div class="panel-content richtext <?= $css ?>">
        <?php
        if ($status === 'view') {
            echo $this->element('users/register-form');
        } elseif ($status === 'fail: email') { ?>
            <h1>
                Bestätigungs-Email konnte nicht versandt werden
            </h1>
            <p>
                Bitte wenden Sie sich an einen Administrator.
            </p>
        <?php } elseif ($status === 'success') { ?>
            <h1>
                Vielen Dank für Ihre Registrierung
            </h1>
            <p>
                Ihnen wurde eine Bestätigungs-Email zugesendet. Bitte klicken
                Sie innerhalb der nächsten 24 Stunden auf den Link in dieser
                Email.
            </p>
            <p>
                Vorher ist eine Anmeldung im Forum nicht möglich!
            </p>
        <?php } ?>
    </div>
</div>
