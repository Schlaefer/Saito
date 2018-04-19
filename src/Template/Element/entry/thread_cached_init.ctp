<?php
use \Stopwatch\Lib\Stopwatch;

Stopwatch::start('entries/thread_cached_init');

$allowThreadCollapse = $allowThreadCollapse ?? false;

/*
 * Caching the localized threadbox title tags.
 * Depending on the number of threads on the page i10n can cost several ms.
 */
$l10nCache = [
    'mix' => __('btn-showThreadInMixView'),
    'collapse' => __('gn.btn.collapse.t'),
    'more' => __('gn.btn.more.t'),
];
$toolboxButtonsToDisplay = ['mix' => 1, 'panel-info' => false];

if (!isset($toolboxButtons)) {
    $toolboxButtons = [];
}
$toolboxButtons += $toolboxButtonsToDisplay;

foreach ($entriesSub as $entrySub) :
    // the entry currently viewed (e.g. entries/view)
    $currentEntry = null;
    if (isset($entry)) {
        $currentEntry = $entry->get('id');
    }
    $rendered = $this->Posting->renderThread(
        $entrySub,
        ['currentEntry' => $currentEntry]
    );
    $css = ($entrySub->getThread()->get('root')->isIgnored()) ? 'ignored' : '';
    ?>
    <div class="threadBox <?= $css ?>" data-id="<?= $entrySub->get('id') ?>">
        <div class="l-table">
            <div class="l-table-row">
                <?php if ($toolboxButtons['panel-info']) : ?>
                    <div class="threadBox-tools l-table-cell panel-info">
                        <a href="<?= $this->request->getAttribute('webroot') ?>entries/mix/<?= $entrySub->get('tid') ?>" class="btn-threadBox-tools" rel="nofollow">
                            <?= $l10nCache['mix']; ?>
                        </a>
                        <?php
                        /**
                         * More menu
                         */
                        $button1 = <<<EOF
<a href="#" class="btn-threadCollapse">
     <i class="fa fa-thread-open"></i> &nbsp; {$l10nCache['collapse']}
</a>
EOF;
                        $style = '';
                        if (!$allowThreadCollapse || !$entrySub->hasAnswers()) {
                            $style = 'visibility: hidden;';
                        }
                        echo $this->Layout->dropdownMenuButton(
                            [$button1],
                            [
                                'title' => $l10nCache['more'] . '&nbsp;<i class="fa fa-caret-down"></i>',
                                'class' => 'btnLink panel-footer-form-btn nbsp',
                                'style' => $style
                            ]
                        );
                        ?>
                    </div>
                <?php endif; ?>

                <div class="threadBox-body panel l-table-cell-main">
                    <div class="threadBox-threadTree">
                        <?= $rendered; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endforeach; ?>
<?php Stopwatch::stop('entries/thread_cached_init'); ?>
