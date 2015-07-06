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
    ) ?>
    <div class="panel-content richtext">
        <?php if ($status === 'activated') : ?>
            <h2>Success</h2>
            <p>
                Your registration is now finished.
            </p>
            <p>
                <?= $this->Html->link('Have fun!', '/') ?>
            </p>
        <?php elseif ($status === 'already') : ?>
            <h2>Already Registered</h2>
            <p>
                The registration was already finished in the past.
            </p>
        <?php
        else : ?>
            <h2>Registration Failed</h2>
            <p>
                The registration wasn't confirmed. Please check that:
            </p>
            <ul>
                <li>
                    the URL is correct
                </li>
                <li>
                    the registration was made within the last 24 hours
                </li>
            </ul>
        <?php endif; ?>
    </div>
</div>
