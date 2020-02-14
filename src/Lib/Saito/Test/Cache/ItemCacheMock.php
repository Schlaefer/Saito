<?php
declare(strict_types=1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace App\Lib\Saito\Test\Cache;

use Saito\Cache\ItemCache;
use Saito\Cache\SaitoCacheEngineInterface;

/**
 * Mock for item cache
 * @package App\Lib\Saito\Test\Cache
 */
class ItemCacheMock extends ItemCache
{
    /**
     * Set Cache Engine
     * @param \Saito\Cache\SaitoCacheEngineInterface $CacheEngine cache engine
     * @return void
     */
    public function setCacheEngine(SaitoCacheEngineInterface $CacheEngine): void
    {
        $this->_CacheEngine = $CacheEngine;
    }

    /**
     * Set raw
     * @param array $data data
     * @return void
     */
    public function setRaw(array $data): void
    {
        $this->_cache = $data;
    }

    /**
     * Write
     * @return void
     */
    public function write(): void
    {
        $this->_write();
    }
}
