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

use Cake\Cache\Cache;

class SaitoCacheEngineAppCache implements SaitoCacheEngineInterface
{
    /**
     * {@inheritDoc}
     */
    public function read($key)
    {
        return Cache::read($key);
    }

    /**
     * {@inheritDoc}
     */
    public function write($key, $data)
    {
        Cache::write($key, $data);
    }
}
