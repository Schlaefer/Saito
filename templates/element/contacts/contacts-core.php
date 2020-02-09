<?php
/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 * @var \App\View\AppView $this
 */
?>
<div class="form-group">
<?= $this->Form->control('subject', [
    'class' => 'form-control',
    'label' => __('user_contact_subject'),
    'tabindex' => 1,
]) ?>
</div>

<div class="form-group">
<?= $this->Form->control('text', [
    'class' => 'form-control',
    'style' => 'height: 10em',
    'label' => __('user_contact_message'),
    'tabindex' => 2,
]) ?>
</div>

<div class="form-group form-check">
<?= $this->Form->control('cc', [
    'class' => 'form-check-input',
    'label' => [
        'class' => 'form-check-label',
        'text' => __('user_contact_send_carbon_copy'),
        'style' => 'display: inline;',
    ],
    'tabindex' => 3,
]) ?>
</div>

<div class="form-group">
<?= $this->Form->submit(__('Submit'), [
    'class' => 'btn btn-primary',
    'tabindex' => 4,
]) ?>
</div>
