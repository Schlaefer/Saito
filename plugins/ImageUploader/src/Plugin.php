<?php
declare(strict_types=1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace ImageUploader;

use Cake\Cache\Cache;
use Cake\Core\BasePlugin;
use Cake\Core\Configure;
use Cake\Core\PluginApplicationInterface;

class Plugin extends BasePlugin
{
    /**
     * {@inheritdoc}
     */
    public function bootstrap(PluginApplicationInterface $app): void
    {
        // Add constants, load configuration defaults.
        // By default will load `config/bootstrap.php` in the plugin.
        parent::bootstrap($app);

        self::configureCache();
    }

    /**
     * Configures the thumbnail cache
     *
     * @return void
     */
    public static function configureCache(): void
    {
        $cacheKey = Configure::read('Saito.Settings.uploader')->getCacheKey();
        if (Cache::getConfig($cacheKey) !== null) {
            return;
        }

        Cache::setConfig(
            $cacheKey,
            [
                'className' => 'File',
                'prefix' => 'saito_thumbnails-',
                'path' => CACHE,
                'groups' => ['uploads'],
                'duration' => '+1 year',
            ]
        );
    }
}
