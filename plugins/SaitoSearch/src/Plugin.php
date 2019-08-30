<?php

declare(strict_types=1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

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

        $app->addPlugin('Search');
    }
}
