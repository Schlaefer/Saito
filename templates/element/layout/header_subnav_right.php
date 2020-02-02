<?php
echo $this->fetch('headerSubnavRightTop');
$this->assign('headerSubnavRightTop', '');
echo $this->fetch('headerSubnavRight');

// if a page has a global paginator we assume it's always shown top right
$paging = $this->request->getParam('paging');
if ($paging) {
    if ($this->request->getParam('action') === 'index') {
        $this->Paginator->options(
            ['url' => ['direction' => null, 'sort' => null, 'limit' => null]]
        );
        $templates = ['counterPages' => '{{page}}'];
    }
    echo $this->element('layout/paginator_control', ['templates' => $templates ?? []]);
}
