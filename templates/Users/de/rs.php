<?php
$this->start('headerSubnavLeft');
echo $this->Layout->navbarBack();
$this->end();
?>
<div class="panel">
    <?=
    $this->Layout->panelHeading(
        __('register_linkname'),
        ['pageHeading' => true]
    )
?>
    <div class="panel-content richtext">
        <?php if ($status === 'activated') : ?>
            <h2>Registrierung abgeschlossen</h2>
            <p>
                Ihre Registrierung ist damit abgeschlossen.
            </p>
            <p>
                <?= $this->Html->link('Viel Spaß!', '/') ?>
            </p>
        <?php elseif ($status === 'already') : ?>
            <h2>Benutzer bereits aktiv</h2>
            <p>
                Der Benutzer wurde bereits in der Vergangenheit aktiviert.
            </p>
            <?php
        else : ?>
            <h2>Aktivierung fehlgeschlagen</h2>
            <p>
                Eine Aktivierung konnte nicht durchgeführt werden. Bitte prüfen
                Sie, dass
            </p>
            <ul>
                <li>
                    die aufgerufene URL korrekt ist
                </li>
                <li>
                    die Registrierung innerhalb der letzten 24 Stunden erfolgt
                    ist
                </li>
            </ul>
        <?php endif; ?>
    </div>
</div>
