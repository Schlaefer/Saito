<?php

declare(strict_types = 1);

namespace SaitoSearch;

use Cake\Core\BasePlugin;
use Cake\Core\PluginApplicationInterface;
use Cake\Core\Plugin as PluginCore;
use Cake\Routing\RouteBuilder;

class Plugin extends BasePlugin
{
    /**
     * {@inheritDoc}
     */
    public function bootstrap(PluginApplicationInterface $app)
    {
        parent::bootstrap($app);

        PluginCore::load('Search');
    }
}
