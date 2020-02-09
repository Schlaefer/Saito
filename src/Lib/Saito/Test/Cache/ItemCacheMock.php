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

class ItemCacheMock extends ItemCache
{
    public function setCacheEngine($CacheEngine)
    {
        $this->_CacheEngine = $CacheEngine;
    }

    public function setRaw($data)
    {
        $this->_cache = $data;
    }

    public function write()
    {
        $this->_write();
    }
}
