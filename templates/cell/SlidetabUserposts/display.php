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
<div class="btn-slidetabRecentposts">
    <i class="fa fa-book fa-lg"></i>
</div>
<?php $this->end('slidetab-tab-button'); ?>
<?php $this->start('slidetab-content'); ?>
<div class="slidetab-header">
    <h4>
    <span
        title='The sea was angry that day my friends, like an old man trying to send back soup in a deli …'>
        <?= h(__('user.recentposts.t', [$CurrentUser->get('username')])); ?>
    </span>
    </h4>
</div>
<div class="slidetab-content">
    <?php if (!empty($recentPosts)) : ?>
        <ul class="slidetab_tree">
            <?php foreach ($recentPosts as $entry) : ?>
                <li>
                    <i class="fa fa-thread"></i>
                    <?= $this->Posting->getFastLink($entry) ?>
                    <br/>
                    <span class='c_info_text'>
                        <?php echo $this->TimeH->formatTime($entry->get('time')); ?>
                    </span>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</div>
<?php $this->end('slidetab-content'); ?>
<?= $this->element('cell/slidetabs'); ?>
