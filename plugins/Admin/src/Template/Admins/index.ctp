<?php

use Cake\Core\Configure;
use Cake\Routing\Router;

$this->Breadcrumbs->add(__('admin.sysInfo.h'), false);

?>
<div id="admins_index" class="admins index">
    <h1>
        <?= __('admin.sysInfo.h') ?>
    </h1>
    <?php
    $version = $this->Html->link(
        __('admin.sysInfo.version', $this->Admin->badge(Configure::read('Saito.v'))),
        Cake\Core\Configure::read('Saito.saitoHomepage'),
        ['escape' => false]
    );
    $si = [
        $version,
        __('admin.sysInfo.server', $this->Admin->badge(Router::fullBaseUrl())),
        __('admin.sysInfo.baseUrl', $this->Admin->badge($this->request->getAttribute('webroot'))),
        __('admin.sysInfo.cce', $this->Admin->badgeForCache('_cake_core_')),
        __('admin.sysInfo.cse', $this->Admin->badgeForCache('default')),
    ];
    $si[] = $this->Html->link(
        __('PHP Info'),
        [
            'controller' => 'admins',
            'action' => 'phpinfo',
            'plugin' => 'admin'
        ]
    );
    echo $this->Html->nestedList($si)
    ?>
</div>
<hr/>
<?=
$this->Html->link(
    __('Empty Caches'),
    ['controller' => 'admins', 'action' => 'emptyCaches', 'plugin' => 'admin'],
    ['class' => 'btn btn-warning']
)
?>
