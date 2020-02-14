<?php
declare(strict_types=1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace Saito\Cache;

abstract class CacheSupportCachelet implements CacheSupportCacheletInterface
{
    /**
     * get cachelet id
     *
     * @return mixed
     */
    public function getId()
    {
        if (!empty($this->_title)) {
            return $this->_title;
        }

        return preg_replace(
            '/Saito\\\Cache\\\(.*)CacheSupportCachelet/',
            '\\1',
            static::class
        );
    }
}
