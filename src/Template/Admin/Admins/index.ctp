<?php

use Cake\Cache\Cache;
use Cake\Core\Configure;
use Cake\Routing\Router;

?>
<div id="admins_index" class="admins index">
    <h1>
        <?= __('admin.sysInfo.h') ?>
    </h1>
    <?php
    $cacheEngine = function ($name) {
        $class = get_class(Cache::engine($name));
        $class = explode('\\', $class);

        return str_replace('Engine', '', end($class));
    };
    $si = [
        __('admin.sysInfo.version', $this->Admin->badge(Configure::read('Saito.v'))),
        __('admin.sysInfo.server', $this->Admin->badge(Router::fullBaseUrl())),
        __('admin.sysInfo.baseUrl', $this->Admin->badge($this->request->getAttribute('webroot'))),
        __('admin.sysInfo.cce', $this->Admin->badge($cacheEngine('_cake_core_'), '_cacheBadge')),
        __('admin.sysInfo.cse', $this->Admin->badge($cacheEngine('default'), '_cacheBadge')),
    ];
    $si[] = $this->Html->link(
        __('PHP Info'),
        [
            'controller' => 'admins',
            'action' => 'phpinfo',
            'prefix' => 'admin'
        ]
    );
    echo $this->Html->nestedList($si)
    ?>
</div>
<hr/>
<?=
$this->Html->link(
    __('Empty Caches'),
    ['controller' => 'admins', 'action' => 'emptyCaches', 'prefix' => 'admin'],
    ['class' => 'btn']
)
?>
