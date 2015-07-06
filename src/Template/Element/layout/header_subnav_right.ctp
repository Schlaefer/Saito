<?php
echo $this->fetch('headerSubnavRightTop');
echo $this->assign('headerSubnavRightTop', '');
echo $this->fetch('headerSubnavRight');

// if a page has a global paginator we assume it's always shown top right
if (isset($this->request->params['paging'])) {
    $options = [];
    if ($this->request->params['action'] === 'index') {
        $this->Paginator->options(
            ['url' => ['direction' => null, 'sort' => null]]
        );
        $options = ['format' => '{{page}}'];
    }
    echo $this->element('layout/paginator_control', ['options' => $options]);
}
