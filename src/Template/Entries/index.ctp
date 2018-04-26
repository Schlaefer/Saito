<?php
use \Stopwatch\Lib\Stopwatch;

Stopwatch::start('entries/index');

if ($this->Paginator->current() === 1) {
    $cUrl = $this->Url->build('/', true);
    $seo = '<link rel="canonical" href="' . $cUrl . '"/>';
} else {
    $seo = '<meta name="robots" content="noindex, follow">';
}
$this->append('meta', $seo);

$this->start('headerSubnavLeft');

echo $this->Layout->navbarItem(
    $this->Layout->textWithIcon(h(__('new_entry_linkname')), 'plus'),
    '/entries/add',
    [
        'class' => 'btn-entryAdd',
        'escape' => false,
        'rel' => 'nofollow'
    ]
);
$this->end();

$this->start('headerSubnavCenter');
echo $this->Layout->navbarItem(
    $this->Layout->textWithIcon('', 'refresh'),
    '#',
    [
        'id' => 'btn-manuallyMarkAsRead',
        'escape' => false,
        'class' => 'btn-hf-center shp',
        'position' => '',
        'data-shpid' => 2
    ]
);
$this->end();

$this->start('headerSubnavRightTop');
if (isset($categoryChooser)) :
    // category-chooser link
    if (isset($categoryChooser[$categoryChooserTitleId])) {
        $title = $categoryChooser[$categoryChooserTitleId];
    } else {
        $title = $categoryChooserTitleId;
    }
    echo $this->Html->link(
        $this->Layout->textWithIcon($title, 'tags'),
        '#',
        [
            'id' => 'btn-category-chooser',
            'class' => 'navbar-item right',
            'escape' => false
        ]
    );
    echo $this->element('entry/category-chooser');
endif;
$this->end();

echo $this->Html->div(
    'entry index',
    $this->element(
        'entry/thread_cached_init',
        [
            'entriesSub' => $entries,
            'toolboxButtons' => ['panel-info' => true]
        ]
    )
);

Stopwatch::stop('entries/index');
