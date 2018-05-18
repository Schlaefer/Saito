<?php

$this->Flash->render();
// @td after full js refactoring and moving getAppJs to the page bottom
// this should go into View/Users/login.ctp again
$this->Flash->render('auth', ['element' => 'Flash/warning']);

if (isset($CurrentUser)) {
    echo $this->Html->scriptBlock($this->JsData->getAppJs($this, $CurrentUser));
}

echo $this->Html->script([
    '../dist/vendor.bundle.js',
    '../dist/app.bundle.js',
]);

echo $this->fetch('script-head');
