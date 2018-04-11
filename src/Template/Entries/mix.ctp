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
    <?php
    echo $this->Html->link(
        '<div class="btn-strip btn-strip-back">&nbsp;</div>',
        $paginatedIndexId,
        [
            'escape' => false,
            'rel' => 'nofollow',
        ]
    );
    ?>
    <div style="margin-left: 25px;">
        <?= $this->Posting->renderThread($entries, ['renderer' => 'mix']) ?>
    </div>
</div>

<?php Stopwatch::stop('entries/mix'); ?>
