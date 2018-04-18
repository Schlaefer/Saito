<?php
$this->start('headerSubnavLeft');
echo $this->Layout->navbarBack();
$this->end();

$title = __('RSS Feeds');
$this->set('titleForPage', $title);
?>
<div class="panel">
    <?= $this->Layout->panelHeading($title, ['pageHeading' => true]) ?>
    <h2><?= $title ?></h2>
    <div class="panel-content richtext">
        <?= $this->Html->nestedList([
            $this->Html->link(__d('feeds', 'postings.new.t'), '/feeds/postings/new.rss'),
            $this->Html->link(__d('feeds', 'threads.new.t'), '/feeds/postings/threads.rss')
        ]); ?>
    </div>
</div>
