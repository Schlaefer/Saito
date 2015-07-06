<?php
$this->start('headerSubnavLeft');
echo $this->Layout->navbarBack();
$this->end();

$title = __('RSS Feeds');
$this->set('titleForPage', $title);
?>
<div class="panel">
    <?= $this->Layout->panelHeading($title, ['pageHeading' => true]) ?>
    <div class="panel-content richtext">
        <?= $this->Html->nestedList([
            $this->Html->link(__('RSS Feed') . ' – ' . __('Last entries'), '/feed/postings.rss'),
            $this->Html->link(__('RSS Feed') . ' – ' . __('Last started threads'), '/feed/threads.rss')
        ]); ?>
    </div>
</div>
