<?php
use Stopwatch\Lib\Stopwatch;

Stopwatch::start('entries/thread_cached_init');

/*
 * Caching the localized threadbox title tags.
 * Depending on the number of threads on the page i10n can cost several ms.
 */
$l10nCache = [
    'mix' => h(__('gn.btn.mix.t')),
    'collapse' => h(__('gn.btn.collapse.t')),
];

if (!isset($toolboxButtons)) {
    $toolboxButtons = [];
}
$toolboxButtons += ['collapse' => true];

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
            <div class="threadBox-tools">
                <?php
                $class = 'infoText';
                if ($toolboxButtons['collapse'] && $entrySub->hasAnswers()) {
                    $class = 'btn-threadCollapse';
                }
                ?>
                <button href="#" class="btn btn-link threadBox-tools-btn <?= $class ?>">
                    <i class="fa fa-fw fa-thread-open" title="<?= $l10nCache['collapse'] ?>";></i>
                </button>

                <a href="<?= $this->Posting->urlToMix($entrySub, false)?>"
                    class="btn btn-link threadBox-tools-btn"
                    title="<?= $l10nCache['mix'] ?>"
                    rel="nofollow">
                        <i class="fa fa-fw fa-mix"></i>
                    </a>
            </div>

        <div class="threadBox-body">
            <div class="threadBox-threadTree">
                <?= $rendered; ?>
            </div>
        </div>
    </div>
<?php endforeach; ?>
<?php Stopwatch::stop('entries/thread_cached_init'); ?>
