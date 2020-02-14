<?php
/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

$this->Breadcrumbs->add(__('Plugins'), false);

echo $this->Html->tag('h1', __('Plugins'));

if ($plugins) {
    $list = [];
    foreach ($plugins as $plugin) {
        $list[] = $this->Html->link($plugin['title'], $plugin['url']);
    }
    echo $this->Html->nestedList($list);
}
