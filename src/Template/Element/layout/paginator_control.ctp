<?php
/**
 * default paginator if pagination is defined
 */
$paginatorControl = $this->fetch('paginatorControl');
// ignore this paginator if a custom one is set
if (!empty($paginatorControl)) {
    echo $paginatorControl;

    return;
}

$templates = $templates ?? [];
$templates = $templates + [
    'counter' => '{{text}}',
    'counterPages' => '{{page}}/{{pages}}',
    'first' => '<a href="{{url}}" class="btn btn-text">{{text}}</a>',
    'nextActive' => '<a rel="next" class="btn btn-text" href="{{url}}">{{text}}</a>',
    'prevActive' => '<a rel="prev" class="btn btn-text" href="{{url}}">{{text}}</a>',
];

$this->Paginator->setConfig('templates', $templates);

if ($this->Paginator->current() > 2) {
    $paginatorControl .= $this->Paginator->first(
        '<i class="fa fa-chevron-left"></i><i class="fa fa-chevron-left"></i>',
        ['escape' => false]
    );
}

if ($this->Paginator->hasPrev()) {
    $paginatorControl .= $this->Paginator->prev(
        '<i class="fa fa-chevron-left"></i>',
        ['escape' => false]
    );
}

$paginatorControl .= $this->Paginator->counter();

if ($this->Paginator->hasNext()) {
    $paginatorControl .= $this->Paginator->next(
        '<i class="fa fa-chevron-right"></i>',
        ['escape' => false]
    );
}

// caches head-nav paginator for footer-nav
$this->assign('paginatorControl', $paginatorControl);

echo $paginatorControl;
