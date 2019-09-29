<?php

$this->Flash->render();

echo $this->Html->scriptBlock($this->JsData->getAppJs($this, $CurrentUser));

echo $this->Html->script([
    'vendor.bundle.js',
    'app.bundle.js',
]);

echo $this->Html->scriptBlock('window.Application.start({ SaitoApp: SaitoApp });');

echo $this->fetch('script-head');
