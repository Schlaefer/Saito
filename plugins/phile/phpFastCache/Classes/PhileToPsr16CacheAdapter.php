<?php
/**
 * Adapter to use PSR-16 compatible cache class with Phile
 */

namespace Phile\Plugin\Phile\PhpFastCache;

use Psr\SimpleCache\CacheInterface;

/**
 * Class PhpFastCache
 *
 * @author  PhileCMS
 * @link    https://philecms.github.io
 * @license http://opensource.org/licenses/MIT
 * @package Phile\Plugin\Phile\PhpFastCache
 */
class PhileToPsr16CacheAdapter implements \Phile\ServiceLocator\CacheInterface
{
    /** @var string slug */
    const SLUG_PREFIX = '-phile.phpFastCache.slug-';
    
    const SLUG = ['{', '}', '(', ')', '/', '\\', '@', ':'];

    /**
     * @var CacheInterface the cache engine
     */
    protected $cacheEngine;

    /**
     * the constructor
     *
     * @param CacheInterface $cacheEngine
     */
    public function __construct(CacheInterface $cacheEngine)
    {
        $this->cacheEngine = $cacheEngine;
    }

    /**
     * method to check if cache has entry for given key
     *
     * @param string $key
     *
     * @return bool
     */
    public function has($key)
    {
        $key = $this->slug($key);
        return $this->cacheEngine->has($key);
    }

    /**
     * method to get cache entry
     *
     * @param string $key
     *
     * @return mixed|null
     */
    public function get($key)
    {
        $key = $this->slug($key);
        return $this->cacheEngine->get($key);
    }

    /**
     * method to set cache entry
     *
     * @param string $key
     * @param mixed $value
     * @param int    $time
     * @param array  $options deprecated
     *
     * @return mixed|void
     */
    public function set($key, $value, $time = 300, array $options = array())
    {
        if (!empty($options)) {
            // not longer supported by phpFastCache
            trigger_error('Argument $options is deprecated and ignored.', E_USER_WARNING);
        }
        $key = $this->slug($key);
        $this->cacheEngine->set($key, $value, $time);
    }

    /**
     * method to delete cache entry
     *
     * @param string $key
     * @param array  $options deprecated
     *
     * @return mixed|void
     */
    public function delete($key, array $options = array())
    {
        if (!empty($options)) {
            // not longer supported by phpFastCache
            trigger_error('Argument $options is deprecated and ignored.', E_USER_WARNING);
        }
        $key = $this->slug($key);
        $this->cacheEngine->delete($key);
    }

    /**
     * clean complete cache and delete all cached entries
     */
    public function clean()
    {
        $this->cacheEngine->clear();
    }

    /**
     * replaces chars forbidden in PSR-16 cache-keys
     *
     * @param string $key key to slug
     * @return string $key slugged key
     */
    protected function slug(string $key): string
    {
        $replacementTokens = array_map(
            function ($key) {
                return self::SLUG_PREFIX . $key;
            },
            array_keys(self::SLUG)
        );
        return str_replace(self::SLUG, $replacementTokens, $key);
    }
}
