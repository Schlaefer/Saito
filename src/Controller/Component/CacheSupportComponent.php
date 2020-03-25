<?php

declare(strict_types=1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace App\Controller\Component;

use Cake\Controller\Component;
use Cake\Event\Event;
use Saito\Cache\CacheSupport;
use Saito\Cache\ItemCache;
use Saito\Cache\LineCacheSupportCachelet;
use Saito\Cache\SaitoCacheEngineAppCache;

class CacheSupportComponent extends Component
{
    /** @var CacheSupport */
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
        $this->_initLineCache();
    }

    /**
     * Clears out all caches
     *
     * @return void
     */
    public function clear()
    {
        $this->_CacheSupport->clear();
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
            new SaitoCacheEngineAppCache(),
            // duration: update relative time values in HTML at least every hour
            ['duration' => 3600, 'maxItems' => 600]
        );
        $this->_CacheSupport->add(
            new LineCacheSupportCachelet($this->LineCache)
        );
    }

    /**
     * {@inheritDoc}
     */
    public function beforeRender(Event $event)
    {
        $event->getSubject()->set('LineCache', $this->LineCache);
    }
}
