<?php

use Cake\Core\Configure;

$require = $require ?? true;
echo $this->Layout->jQueryTag();

$this->Flash->render();
// @td after full js refactoring and moving getAppJs to the page bottom
// this should go into View/Users/login.ctp again
$this->Flash->render('auth', ['element' => 'Flash/warning']);

if (isset($CurrentUser)) {
    echo $this->Html->scriptBlock($this->JsData->getAppJs($this, $CurrentUser));
}

echo $this->fetch('script-head');

if ($require) {
    $requireJsScript = 'main';
    if (!Configure::read('debug')) {
        $requireJsScript .= '.min';
    }
    echo $this->RequireJs->scriptTag($requireJsScript);
}
