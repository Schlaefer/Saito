<?php
$this->start('headerSubnavLeft');
echo $this->Layout->navbarBack();
$this->end();
?>
<div class="panel">
    <?= $this->Layout->panelHeading(__('Bookmarks'), ['pageHeading' => true]) ?>
    <div class="panel-content">
        <div id="bookmarks">
            <?php if ($bookmarks) : ?>
            <div class="l-bookmarks-container">
                <div class="l-bookmarks-row bookmarks-row-header">
                    <div class="l-bookmarks-cell bookmarks-cell"
                         style="width: 4%">
                    </div>
                    <div class="l-bookmarks-cell bookmarks-cell"
                         style="width: 60%">
                        <?php echo __('Subject'); ?>
                    </div>
                    <div class="l-bookmarks-cell bookmarks-cell"
                         style="width: 4%; padding-left: 0">
                    </div>
                    <div class="l-bookmarks-cell bookmarks-cell"
                         style="width: 30%">
                        <?php echo __('Comment'); ?>
                    </div>
                </div>
                <?php
                foreach ($bookmarks as $bookmark) { ?>
                    <div class="l-bookmarks-row js-bookmark" data-id="<?= $bookmark->get('id') ?>">
                        <div class="l-bookmarks-cell bookmarks-cell" style="width: 4%">
                            <?=
                            $this->Html->link(
                                '<i class="fa fa-trash-o fa-lg"></i>',
                                '#',
                                [
                                    'class' => 'btn-bookmark-delete',
                                    'escape' => false,
                                    'title' => __('Delete'),
                                ]
                            );
                            ?>
                        </div>
                        <div id="<?= $bookmark->get('entry')->get('id') ?>"
                             class="l-bookmarks-cell bookmarks-cell"
                             style="width: 60%">
                            <?php
                            $posting = \Saito\App\Registry::newInstance(
                                '\Saito\Posting\Posting',
                                [
                                    'rawData' => $bookmark->get('entry')
                                        ->toArray()
                                ]
                            );
                            echo $this->Posting->renderThread(
                                $posting,
                                ['rootWrap' => true]
                            );
                            ?>
                        </div>
                        <div class="l-bookmarks-cell bookmarks-cell"
                             style="width: 4%; padding-left: 0">
                            <?=
                            $this->Html->link(
                                '<i class="fa fa-edit fa-lg"></i>',
                                ['action' => 'edit', $bookmark->get('id')],
                                [
                                    'escape' => false,
                                    'title' => __('btn-comment-title')
                                ]
                            )
                            ?>
                        </div>
                        <div class="l-bookmarks-cell bookmarks-cell"
                             style="width: 30%">
                            <?= h($bookmark->get('comment')) ?>
                        </div>
                    </div>
                <?php } ?>
            <?php endif; ?>
            </div>
        </div>
    </div>
</div>
