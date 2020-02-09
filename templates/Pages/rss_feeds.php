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

$title = __('s.rss.t');
$this->set('titleForPage', $title);
?>
<div class="card panel-center">
    <div class="card-header">
        <?= $this->Layout->panelHeading($title) ?>
    </div>
    <div class="card-body panel-content richtext">
        <?= $this->Html->nestedList([
            $this->Html->link(__d('feeds', 'postings.new.t'), '/feeds/postings/new.rss'),
            $this->Html->link(__d('feeds', 'threads.new.t'), '/feeds/postings/threads.rss'),
        ]); ?>
    </div>
</div>
