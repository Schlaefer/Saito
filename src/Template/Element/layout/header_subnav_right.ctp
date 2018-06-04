<?php
echo $this->fetch('headerSubnavRightTop');
$this->assign('headerSubnavRightTop', '');
echo $this->fetch('headerSubnavRight');

// if a page has a global paginator we assume it's always shown top right
$paging = $this->request->getParam('paging');
if ($paging) {
    $options = [];
    if ($this->request->getParam('action') === 'index') {
        $this->Paginator->options(
            ['url' => ['direction' => null, 'sort' => null, 'limit' => null]]
        );
        $options = ['format' => '{{page}}'];
    }
    echo $this->element('layout/paginator_control', ['options' => $options]);
}
