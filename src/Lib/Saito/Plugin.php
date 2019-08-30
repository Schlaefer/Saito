<?php

declare(strict_types=1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace Saito;

use Cake\Core\Configure;

class Plugin
{

    /**
     * loads defaults from plugin config and merges them with global config
     *
     * allows to override plugin/Config from app/Config
     *
     * @param string $plugin plugin
     * @return array|mixed
     */
    public static function loadConfig($plugin)
    {
        $global = Configure::read($plugin);
        Configure::load("$plugin.config", 'default', false);
        $settings = Configure::read($plugin);
        if (is_array($global)) {
            $settings = $global + $settings;
        }
        Configure::write($plugin, $settings);

        return $settings;
    }
}
