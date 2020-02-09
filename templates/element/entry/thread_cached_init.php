<?php
/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 * @var \App\View\AppView $this
 */
use Stopwatch\Lib\Stopwatch;

Stopwatch::start('entries/thread_cached_init');

$allowThreadCollapse = isset($allowThreadCollapse) ? $allowThreadCollapse : false;
$toolboxButtons = isset($toolboxButtons) ? $toolboxButtons : [];

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
        <?php if ($toolboxButtons['panel-info']) : ?>
            <div class="threadBox-tools lefty-aside">
                <a href="<?= $this->request->getAttribute('webroot') ?>entries/mix/<?= $entrySub->get('tid') ?>" class="btn btn-link" rel="nofollow">
                    <?= $l10nCache['mix']; ?>
                </a>
                <?php
                /**
                 * More menu
                 */
                $button1 = <<<EOF
<a href="#" class="dropdown-item btn-threadCollapse">
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
                        'title' => $l10nCache['more'],
                        'class' => 'btn btn-link',
                        'style' => $style,
                    ]
                );
                ?>
                <div class="clearfix"></div>
            </div>
        <?php endif; ?>

        <div class="threadBox-body panel flex-lefty-item">
            <div class="threadBox-threadTree">
                <?= $rendered; ?>
            </div>
        </div>
    </div>
<?php endforeach; ?>
<?php Stopwatch::stop('entries/thread_cached_init'); ?>
