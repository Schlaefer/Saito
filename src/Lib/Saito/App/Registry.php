<?php

declare(strict_types=1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace Saito\App;

use Aura\Di\Container;
use Aura\Di\ContainerBuilder;
use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use Cron\Lib\Cron;
use Saito\Markup\MarkupSettings;
use Saito\User\Permission\Permissions;

/**
 * Global registry for Saito app.
 *
 * @package Saito\App
 */
class Registry
{
    /**
     * @var Container;
     */
    protected static $dic;

    /**
     * Resets and initializes registry.
     *
     * @return Container
     */
    public static function initialize()
    {
        $dic = (new ContainerBuilder())->newInstance();
        $dic->set('Cron', new Cron());

        $dic->set('Permissions', $dic->lazyNew(Permissions::class));
        $dic->params[Permissions::class]['roles'] = Configure::read('Saito.Roles');
        $dic->params[Permissions::class]['permissionConfig'] = Configure::read('Saito.Permissions');
        $dic->params[Permissions::class]['categories'] = TableRegistry::getTableLocator()->get('Categories');

        $dic->set('AppStats', $dic->lazyNew('\Saito\App\Stats'));

        $dic->set('MarkupSettings', $dic->lazyNew(MarkupSettings::class));
        $markupClass = Configure::read('Saito.Settings.ParserPlugin');

        $dic->set('Markup', $dic->lazyNew($markupClass));
        $dic->params[$markupClass]['settings'] = $dic->lazyGet('MarkupSettings');

        self::$dic = $dic;

        return $dic;
    }

    /**
     * Set object
     *
     * @param string $key $key
     * @param object $object object
     * @return void
     */
    public static function set(string $key, object $object)
    {
        self::$dic->set($key, $object);
    }

    /**
     * Get object
     *
     * @param string $key key
     * @return object
     */
    public static function get(string $key): object
    {
        return self::$dic->get($key);
    }

    /**
     * Get new instance
     *
     * @param string $key $key
     * @param array $params params
     * @param array $setter setter
     * @return object
     */
    public static function newInstance($key, array $params = [], array $setter = []): object
    {
        return self::$dic->newInstance($key, $params, $setter);
    }
}
