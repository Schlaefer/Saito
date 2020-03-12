<?php
use Stopwatch\Lib\Stopwatch;

Stopwatch::start('view.ctp');

/// subnav left
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

/// subnav right
$this->start('headerSubnavRight');
$mixTitle = h(__('gn.btn.mix.t'));
echo $this->Layout->navbarItem(
    $this->Layout->textWithIcon($mixTitle, 'mix'),
    $this->Posting->urlToMix($entry),
    ['rel' => 'nofollow', 'escape' => false, 'title' => $mixTitle]
);
$this->end();

?>
<div class="viewEntry">
    <?= $this->element('/entry/view_posting', ['entry' => $entry]); ?>
    <?=
    $this->element(
        'entry/thread_cached_init',
        ['entriesSub' => [$tree], 'toolboxButtons' => ['collapse' => false]]
    ); ?>
</div>
<?php Stopwatch::stop('view.ctp'); ?>
