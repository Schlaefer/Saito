<?php

$this->Breadcrumbs->add(__('Plugins'), '/admin/plugins');

echo $this->Html->tag('h1', __('Plugins'));

if ($plugins) {
    foreach ($plugins as $plugin) {
        $list[] = $this->Html->link($plugin['title'], $plugin['url']);
    }
    echo $this->Html->nestedList($list);
}
