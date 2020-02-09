<?php
/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 * @var \App\View\AppView $this
 */

$title = 'Nutzungsbedingungen';
$this->set('titleForPage', $title);
?>
<div class="panel">
    <?= $this->Layout->panelHeading($title, ['pageHeading' => true]) ?>
    <div class="panel-content richtext">
        <h1>Nutzungsbedingungen</h1>
    </div>
</div>

