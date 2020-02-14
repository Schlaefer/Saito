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

use Stopwatch\Lib\Stopwatch;

/**
 * Class ItemCache
 *
 * Caches items and saves them persistently. Offers max item age and size
 * constrains.
 */
class ItemCache
{
    /**
     * Cache array
     *
     * @var array|null
     */
    protected $_cache = null;

    /**
     * @var null|\Saito\Cache\SaitoCacheEngineInterface if null cache is only active for this request
     */
    protected $_CacheEngine = null;

    protected $_settings = [
        'duration' => null,
        'maxItems' => null,
        // +/- percentage maxItems can deviate before gc is triggered
        'maxItemsFuzzy' => 0.06,
    ];

    protected $_gcFuzzy;

    protected $_gcMax;

    protected $_gcMin;

    protected $_name;

    protected $_now;

    protected $_oldestPersistent = 0;

    protected $_updated = false;

    /**
     * Constructor
     *
     * @param string $name name
     * @param \Saito\Cache\SaitoCacheEngineInterface $CacheEngine engine
     * @param array $options options
     */
    public function __construct(
        $name,
        ?SaitoCacheEngineInterface $CacheEngine = null,
        $options = []
    ) {
        $this->_settings = $options + $this->_settings;
        $this->_now = time();
        $this->_name = $name;
        $this->_CacheEngine = $CacheEngine;

        if ($this->_settings['maxItems']) {
            $this->_gcFuzzy = $this->_settings['maxItemsFuzzy'];
            $this->_gcMax = (int)($this->_settings['maxItems'] * (1 + $this->_gcFuzzy));
            $this->_gcMin = (int)($this->_gcMax * (1 - $this->_gcFuzzy));
        }
    }

    /**
     * Deconstruct
     *
     * @return void
     */
    public function __destruct()
    {
        if ($this->_cache === null) {
            return;
        }
        $this->_write();
    }

    /**
     * Delete
     *
     * @param string $key key
     * @return void
     */
    public function delete($key)
    {
        if ($this->_cache === null) {
            $this->_read();
        }
        $this->_updated = true;
        unset($this->_cache[$key]);
    }

    /**
     * Getter
     *
     * @param string|null $key key
     * @return null|string|array
     */
    public function get($key = null)
    {
        if ($this->_cache === null) {
            $this->_read();
        }
        if ($key === null) {
            return $this->_cache;
        }
        if (!isset($this->_cache[$key])) {
            return null;
        }

        return $this->_cache[$key]['content'];
    }

    /**
     * compare updated
     *
     * @param string $key key
     * @param int $timestamp timestamp
     * @param callable $comp compare function
     * @return mixed
     * @throws \InvalidArgumentException
     */
    public function compareUpdated($key, $timestamp, callable $comp)
    {
        if (!isset($this->_cache[$key])) {
            throw new \InvalidArgumentException();
        }

        return $comp(
            $this->_cache[$key]['metadata']['content_last_updated'],
            $timestamp
        );
    }

    /**
     * Setter
     *
     * @param string $key key
     * @param mixed $content content
     * @param null $timestamp timestamp
     * @return void
     */
    public function set($key, $content, $timestamp = null)
    {
        if ($this->_cache === null) {
            $this->_read();
        }

        if ($timestamp === null) {
            $timestamp = $this->_now;
        }

        /*
         * Don't fill the cache with entries which are removed by the maxItemsGC.
         * Old entries may trigger a store to disk on each request without
         * adding new cache data.
         */
        // there's no upper limit
        if (!$this->_gcMax) {
            $this->_updated = true;
            // the new entry is not older than the oldest existing
        } elseif ($timestamp > $this->_oldestPersistent) {
            $this->_updated = true;
            // there's still room in lower maxItemsGc limit
        } elseif (count($this->_cache) < $this->_gcMin) {
            $this->_updated = true;
        }

        $metadata = [
            'created' => $this->_now,
            'content_last_updated' => $timestamp,
        ];

        $data = ['metadata' => $metadata, 'content' => $content];
        $this->_cache[$key] = $data;
    }

    /**
     * Is cache full
     *
     * @return bool
     */
    protected function _isCacheFull()
    {
        if (!$this->_gcMax) {
            return false;
        }

        return count($this->_cache) >= $this->_settings['maxItems'];
    }

    /**
     * read
     *
     * @return void
     */
    protected function _read()
    {
        if ($this->_CacheEngine === null) {
            $this->_cache = [];

            return;
        }
        Stopwatch::start("ItemCache read: {$this->_name}");
        $this->_cache = $this->_CacheEngine->read($this->_name);
        if (empty($this->_cache)) {
            $this->_cache = [];
        }
        if ($this->_settings['duration']) {
            $this->_gcOutdated();
        }
        if (count($this->_cache) > 0) {
            $oldest = reset($this->_cache);
            $this->_oldestPersistent = $oldest['metadata']['content_last_updated'];
        }
        Stopwatch::stop("ItemCache read: {$this->_name}");
    }

    /**
     * Reset
     *
     * @return void
     */
    public function reset()
    {
        $this->_updated = true;
        $this->_cache = [];
    }

    /**
     * gc outdated
     *
     * @return void
     */
    protected function _gcOutdated()
    {
        Stopwatch::start("ItemCache _gcOutdated: {$this->_name}");
        $expired = time() - $this->_settings['duration'];
        foreach ($this->_cache as $key => $item) {
            if ($item['metadata']['created'] < $expired) {
                unset($this->_cache[$key]);
                $this->_updated = true;
            }
        }
        Stopwatch::stop("ItemCache _gcOutdated: {$this->_name}");
    }

    /**
     * garbage collection max items
     *
     * costly function for larger arrays, relieved by maxItemsFuzzy
     *
     * @return void
     */
    protected function _gcMaxItems()
    {
        if (count($this->_cache) <= $this->_gcMax) {
            return;
        }
        $this->_cache = array_slice($this->_cache, 0, $this->_gcMin, true);
    }

    /**
     * sorts for 'content_last_updated', oldest on top
     *
     * @return void
     */
    protected function _sort()
    {
        // keep items which were last used/updated
        uasort(
            $this->_cache,
            function ($a, $b) {
                if ($a['metadata']['content_last_updated'] === $b['metadata']['content_last_updated']) {
                    return 0;
                }

                return $a['metadata']['content_last_updated'] < $b['metadata']['content_last_updated'] ? 1 : -1;
            }
        );
    }

    /**
     * Write
     *
     * @return void
     */
    protected function _write()
    {
        if ($this->_CacheEngine === null || !$this->_updated) {
            return;
        }
        $this->_sort();
        if ($this->_gcMax) {
            $this->_gcMaxItems();
        }
        $this->_CacheEngine->write($this->_name, $this->_cache);
    }
}
