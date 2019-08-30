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
use Cake\Core\Configure;
use Cron\Lib\Cron;
use Saito\Markup\MarkupSettings;

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
    protected static $_DIC;

    /**
     * Initialize
     *
     * @return Container
     */
    public static function initialize()
    {
        $dic = new Container(new \Aura\Di\Factory);
        $dic->set('Cron', new Cron());
        $dic->set('Permission', $dic->lazyNew('Saito\User\Permission'));
        $dic->set('AppStats', $dic->lazyNew('\Saito\App\Stats'));
        $dic->params['\Saito\Posting\Posting']['CurrentUser'] = $dic->lazyGet('CU');

        $dic->set('MarkupSettings', $dic->lazyNew(MarkupSettings::class));
        $markupClass = Configure::read('Saito.Settings.ParserPlugin');
        ;
        $dic->set('Markup', $dic->lazyNew($markupClass));
        $dic->params[$markupClass]['settings'] = $dic->lazyGet('MarkupSettings');

        self::$_DIC = $dic;

        return $dic;
    }

    /**
     * Set object
     *
     * @param string $key $key
     * @param object $object object
     * @return void
     */
    public static function set($key, $object)
    {
        self::$_DIC->set($key, $object);
    }

    /**
     * Get object
     *
     * @param string $key key
     * @return object
     */
    public static function get($key)
    {
        return self::$_DIC->get($key);
    }

    /**
     * Get new instance
     *
     * @param string $key $key
     * @param array $params params
     * @param array $setter setter
     * @return object
     */
    public static function newInstance(
        $key,
        array $params = [],
        array $setter = []
    ) {
        return self::$_DIC->newInstance($key, $params, $setter);
    }
}
