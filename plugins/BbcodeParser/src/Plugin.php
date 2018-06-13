<?php

declare(strict_types = 1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers 2018
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace BbcodeParser;

use Cake\Cache\Cache;
use Cake\Core\BasePlugin;
use Cake\Core\PluginApplicationInterface;

class Plugin extends BasePlugin
{
    public function bootstrap(PluginApplicationInterface $app)
    {
        // Add constants, load configuration defaults.
        // By default will load `config/bootstrap.php` in the plugin.
        // parent::bootstrap($app);
        Cache::setConfig(
            'bbcodeParserEmbed',
            [
                'className' => 'File',
                'prefix' => 'saito_embed-',
                'path' => CACHE,
                'groups' => ['embed'],
                'duration' => '+1 month'
            ]
        );
    }
}
