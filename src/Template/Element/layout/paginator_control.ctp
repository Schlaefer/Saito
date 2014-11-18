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

SDV($options, []);
$defaults = [
    'format' => '{{page}}/{{pages}}'
];
$options += $defaults;

$paginatorControl = '<ul class="paginator navbar-item right">';
if ($this->Paginator->current() > 2) {
    $paginatorControl .= $this->Paginator->first(
        '<i class="fa fa-chevron-left"></i><i class="fa fa-chevron-left"></i>',
        ['escape' => false, 'style' => 'padding-right: 1em']
    );
}

if ($this->Paginator->hasPrev()) {
    $paginatorControl .= $this->Paginator->prev(
        '<i class="fa fa-chevron-left"></i>',
        ['escape' => false]
    );
}

$counter = $this->Paginator->counter($options);
$paginatorControl .= '<li class="counter">' . $counter . '</li>';

if ($this->Paginator->hasNext()) {
    $paginatorControl .= $this->Paginator->next(
        '<i class="fa fa-chevron-right"></i>',
        ['escape' => false]
    );
}
$paginatorControl .= '</ul>';

// caches head-nav paginator for footer-nav
$this->assign('paginatorControl', $paginatorControl);

echo $paginatorControl;
