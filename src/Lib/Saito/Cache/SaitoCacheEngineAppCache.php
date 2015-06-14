<?php

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
        return Cache::write($key, $data);
    }
}
