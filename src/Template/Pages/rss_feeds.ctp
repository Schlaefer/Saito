<div class="panel">
    <?= $this->Layout->panelHeading(__('RSS Feeds'), ['pageHeading' => true]) ?>
    <div class="panel-content richtext">
        <?= $this->Html->nestedList([
            $this->Html->link(__('RSS Feed') . ' – ' . __('Last entries'), '/feed/postings.rss'),
            $this->Html->link(__('RSS Feed') . ' – ' . __('Last started threads'), '/feed/threads.rss')
        ]); ?>
    </div>
</div>
