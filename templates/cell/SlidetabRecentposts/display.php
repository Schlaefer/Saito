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
<?php $this->start('slidetab-tab-button'); ?>
    <div class="btn-slidetabRecententries">
        <i class="fa fa-clock-o fa-lg"></i>
    </div>
<?php $this->end('slidetab-tab-button'); ?>
<?php $this->start('slidetab-content'); ?>
    <div class="slidetab-header">
        <h4>
            <?= __('Recent entries') ?>
        </h4>
    </div>
<?php if (!empty($recentEntries)) : ?>
    <div class="slidetab-content">
        <ul class="slidetab_tree">
            <?php foreach ($recentEntries as $entry) : ?>
                <li>
                    <i class="fa fa-thread"></i>
                    <?= $this->Posting->getFastLink($entry); ?>
                    <br/>
                                <span class='c_info_text'>
                                    <?= h($entry->get('name')) ?>,
                                    <?=
                                    $this->Time->timeAgoInWords(
                                        $entry->get('time'),
                                        [
                                            'accuracy' => ['hour' => 'hour'],
                                        ]
                                    ); ?>
                                </span>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>
<?php $this->end('slidetab-content'); ?>
<?= $this->element('cell/slidetabs'); ?>
