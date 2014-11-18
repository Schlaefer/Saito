<?php
	use \Stopwatch\Lib\Stopwatch;

  Stopwatch::start('entries/thread_cached_init');

    SDV($allowThreadCollapse, false);
  /*
   * Caching the localized threadbox title tags.
   * Depending on the number of threads on the page i10n can cost several ms.
   */
  $cacheThreadBoxTitlei18n = array(
    'btn-showThreadInMixView' => __('btn-showThreadInMixView'),
    'btn-threadCollapse' => __('btn-threadCollapse'),
  );
$toolboxButtonsToDisplay = [
    'mix' => 1,
    'panel-info' => false
];
  if (!isset($toolboxButtons)) {
      $toolboxButtons = [];
  }
  $toolboxButtons += $toolboxButtonsToDisplay;

  foreach ($entries_sub as $entry_sub) :
    // the entry currently viewed (e.g. entries/view)
    $currentEntry = null;
    if (isset($entry)) {
      $currentEntry = $entry->get('id');
    }
		$rendered = $this->Posting->renderThread($entry_sub,
			['currentEntry' => $currentEntry]);
    $css = ($entry_sub->getThread()->get('root')->isIgnored()) ? 'ignored' : '';
?>
<div class="threadBox <?= $css ?>" data-id="<?= $entry_sub->get('id') ?>">
    <div class="l-table">
        <div class="l-table-row">
            <?php if ($toolboxButtons['panel-info']) : ?>
                <div class="threadBox-tools l-table-cell panel-info">
                    <a href="<?= $this->request->webroot; ?>entries/mix/<?= $entry_sub->get('tid'); ?>" class="btn-threadBox-tools" rel="nofollow">
                        <?= $cacheThreadBoxTitlei18n['btn-showThreadInMixView']; ?>
                    </a>

                    <?php
                    /**
                     * More menu
                     */

                    //          if ($allowThreadCollapse && $entry_sub->hasAnswers()) {
                    $style = 'display: none;';
                    $button1 = <<<EOF
                            <button class="btnLink btn-threadCollapse btn-threadBox-tools"
                                            title="{$cacheThreadBoxTitlei18n['btn-threadCollapse']}">
                                 <i class="fa fa-thread-open"></i>
                                 &nbsp;
                                 collapse
                            </button>
EOF;
                    //          }
                    echo $this->Layout->dropdownMenuButton(
                        [$button1],
                        [

                            'title' => 'more&nbsp;<i class="fa fa-caret-down"></i>',
                            'class' => 'btnLink panel-footer-form-btn nbsp'
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
