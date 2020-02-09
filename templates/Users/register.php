<?php
/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 * @var \App\View\AppView $this
 */

$this->start('headerSubnavLeft');
echo $this->Layout->navbarBack();
$this->end();

$css = ($status === 'view') ? 'panel-form' : '';
?>
<div class="card panel-center">
    <div class="card-header">
        <?=
        $this->Layout->panelHeading(
            __('register_linkname'),
            ['pageHeading' => true]
        ) ?>
    </div>
    <div class="card-body richtext <?= $css ?>">
        <?php
        if ($status === 'view') {
            echo $this->element('users/register-form');
        } elseif ($status === 'fail: email') { ?>
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
                An email with a link was send to you. Please click that link
                within
                the next 24 hours to confirm your registration.
            </p>
            <p>
                You're not able to login until you have confirmed your
                registration!
            </p>
        <?php } ?>
    </div>
</div>
