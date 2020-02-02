<?php
use Stopwatch\Lib\Stopwatch;

Stopwatch::start('view.ctp');

// subnav left
$this->start('headerSubnavLeft');
echo $this->Layout->navbarBack(
    $this->Posting->getPaginatedIndexPageId(
        $entry->get('tid'),
        $referer['action'] ?? null
    ),
    __('back_to_forum_linkname'),
    ['rel' => 'nofollow']
);
$this->end();
?>
<div class="viewEntry">
    <?= $this->element('/entry/view_posting', ['entry' => $entry]); ?>
    <?=
    $this->element(
        'entry/thread_cached_init',
        ['entriesSub' => [$tree], 'toolboxButtons' => ['mix' => true]]
    ); ?>
</div>
<?php Stopwatch::stop('view.ctp'); ?>
