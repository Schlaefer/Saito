<?php

namespace App\Controller\Component;

use Cake\Controller\Component;
use Cake\Core\App;
use Cake\Core\Configure;
use Cake\Event\Event;
use Saito\Cache\CacheSupport;
use Saito\Cache\ItemCache;
use Saito\Cache\LineCacheSupportCachelet;
use Saito\Cache\SaitoCacheEngineAppCache;

class CacheSupportComponent extends Component
{

    protected $_CacheSupport;

    /**
     * @var ItemCache
     */
    public $LineCache;

    /**
     * {@inheritDoc}
     */
    public function initialize(array $config)
    {
        $this->_CacheSupport = new CacheSupport();
        $this->_addConfigureCachelets();
        $this->_initLineCache();
    }

    /**
     * Initialize line-cache.
     *
     * @return void
     */
    protected function _initLineCache()
    {
        $this->LineCache = new ItemCache(
            'Saito.LineCache',
            new SaitoCacheEngineAppCache,
            // duration: update relative time values in HTML at least every hour
            ['duration' => 3600, 'maxItems' => 600]
        );
        $this->_CacheSupport->add(
            new LineCacheSupportCachelet($this->LineCache)
        );
    }

    /**
     * Adds additional cachelets from Configure `Saito.Cachelets`
     *
     * E.g. use in `Plugin/<foo>/Config/bootstrap.php`:
     *
     * <code>
     * Configure::write('Saito.Cachelets.M', ['location' => 'M.Lib', 'name' =>
     * 'MCacheSupportCachelet']);
     * </code>
     *
     * @return void
     */
    protected function _addConfigureCachelets()
    {
        $_additionalCachelets = Configure::read('Saito.Cachelets');
        if (!$_additionalCachelets) {
            return;
        }
        foreach ($_additionalCachelets as $_c) {
            App::uses($_c['name'], $_c['location']);
            $this->_CacheSupport->add(new $_c['name']);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function beforeRender(Event $event)
    {
        $event->getSubject()->set('LineCache', $this->LineCache);
    }

    /**
     * {@inheritDoc}
     */
    public function __call($method, $params)
    {
        $proxy = [$this->_CacheSupport, $method];
        if (is_callable($proxy)) {
            return call_user_func_array($proxy, $params);
        }
    }
}
