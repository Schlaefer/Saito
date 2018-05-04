<?php
use Stopwatch\Lib\Stopwatch;

Stopwatch::start('entries/mix');

$paginatedIndexId = $this->Posting->getPaginatedIndexPageId(
    $entries->get('tid'),
    $referer['action'] ?? null
);

$this->start('headerSubnavLeft');

echo $this->Layout->navbarItem(
    '<i class="fa fa-arrow-left"></i> ' . __('Back'),
    $paginatedIndexId,
    ['escape' => false, 'rel' => 'nofollow']
);
$this->end();
?>

<div class="entry mix" style="position:relative;">
    <?= $this->Posting->renderThread($entries, ['renderer' => 'mix']) ?>
</div>

<?php Stopwatch::stop('entries/mix'); ?>
