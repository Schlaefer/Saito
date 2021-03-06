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
use Cake\Event\Event;
use Cake\Event\EventListenerInterface;
use Cake\Event\EventManager;
use Saito\Event\SaitoEventListener;
use Saito\Event\SaitoEventManager;

class CacheSupport implements EventListenerInterface
{

    protected $_Caches = [];

    protected $_buildInCaches = [
        // php caches
        'ApcCacheSupportCachelet',
        'OpCacheSupportCachelet',
        // application caches
        'EntriesCacheSupportCachelet',
        'CakeCacheSupportCachelet',
        'SaitoCacheSupportCachelet',
    ];

    protected $metaKeys = [
        'Thread' => ['EntriesCache', 'LineCache'],
    ];

    /**
     * Constructor
     */
    public function __construct()
    {
        foreach ($this->_buildInCaches as $_name) {
            $name = 'Saito\Cache\\' . $_name;
            $this->add(new $name());
        }
        EventManager::instance()->on($this);
    }

    /**
     * {@inheritDoc}
     */
    public function implementedEvents()
    {
        return ['Cmd.Cache.clear' => 'onClear'];
    }

    /**
     * Clears out cache by name in $event['cache'];
     *
     * @param Event $event event
     * @return void
     */
    public function onClear(Event $event)
    {
        $cache = $event->getData('cache');
        $id = $event->getData('id');
        $this->clear($cache, $id);
    }

    /**
     * Clear cache
     *
     * @param mixed $cache cache to clear
     *                null: all
     *                string: name of specific cache
     *                array: multiple name strings
     * @param null $id id
     * @return void
     */
    public function clear($cache = null, $id = null)
    {
        if (is_string($cache) && isset($this->metaKeys[$cache])) {
            $cache = $this->metaKeys[$cache];
        }
        if (is_array($cache)) {
            foreach ($cache as $_c) {
                $this->clear($_c, $id);
            }

            return;
        }
        if ($cache === null) {
            foreach ($this->_Caches as $_Cache) {
                $_Cache->clear();
            }
        } else {
            if (isset($this->_Caches[$cache])) {
                $this->_Caches[$cache]->clear($id);
            }
        }
    }

    /**
     * add cachelet
     *
     * @param CacheSupportCacheletInterface $cache cachelet
     * @param string $id id
     * @return void
     */
    public function add(CacheSupportCacheletInterface $cache, $id = null)
    {
        if ($id === null) {
            $id = $cache->getId();
        }
        if (!isset($this->_Caches[$id])) {
            $this->_Caches[$id] = $cache;
        }
    }
}

//@codingStandardsIgnoreStart
interface CacheSupportCacheletInterface
//@codingStandardsIgnoreEnd
{

    /**
     * clear cachelet cache
     *
     * @param string $id id
     * @return void
     */
    public function clear($id = null);

    /**
     * Get cachelet id
     *
     * @return string
     */
    public function getId();
}

//@codingStandardsIgnoreStart
class SaitoCacheSupportCachelet extends CacheSupportCachelet
//@codingStandardsIgnoreEnd
{
    /**
     * {@inheritDoc}
     */
    public function clear($id = null)
    {
        Cache::clear(false, 'default');
        Cache::clear(false, 'long');
        Cache::clear(false, 'short');
    }
}

//@codingStandardsIgnoreStart
class ApcCacheSupportCachelet extends CacheSupportCachelet
//@codingStandardsIgnoreEnd
{

    /**
     * {@inheritDoc}
     */
    public function clear($id = null)
    {
        if (!function_exists('apcu_store')) {
            return;
        }
        apcu_clear_cache();
    }
}

//@codingStandardsIgnoreStart
class OpCacheSupportCachelet extends CacheSupportCachelet
//@codingStandardsIgnoreEnd
{

    /**
     * {@inheritDoc}
     */
    public function clear($id = null)
    {
        if (!function_exists('opcache_reset')) {
            return;
        }
        opcache_reset();
    }
}

//@codingStandardsIgnoreStart
class CakeCacheSupportCachelet extends CacheSupportCachelet
//@codingStandardsIgnoreEnd
{

    protected $_title = 'Cake';

    /**
     * {@inheritDoc}
     */
    public function clear($id = null)
    {
        Cache::clearGroup('persistent');
        Cache::clearGroup('models');
        Cache::clearGroup('views');
    }
}

//@codingStandardsIgnoreStart
class EntriesCacheSupportCachelet extends CacheSupportCachelet implements
//@codingStandardsIgnoreEnd
    EventListenerInterface,
    SaitoEventListener
{

    // only rename if you rename event cmds triggering this cache
    protected $_title = 'EntriesCache';

    protected $_CacheTree;

    /**
     * Constructor.
     */
    public function __construct()
    {
        EventManager::instance()->on($this);
        SaitoEventManager::getInstance()->attach($this);
    }

    /**
     * {@inheritDoc}
     */
    public function implementedEvents()
    {
        return [
            'Model.Thread.change' => 'onThreadChanged',
            'Model.Entry.replyToEntry' => 'onEntryChanged',
            'Model.Entry.update' => 'onEntryChanged',
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function implementedSaitoEvents()
    {
        return [
            'saito.core.posting.delete.after' => 'onDelete',
        ];
    }

    /**
     * on delete
     *
     * @param Event $event event
     * @return void
     */
    public function onDelete($event)
    {
        $this->clear();
    }

    /**
     * on thread changed
     *
     * @param Event $event event
     * @return void
     */
    public function onThreadChanged($event)
    {
        $this->clear();
    }

    /**
     * on entry changed
     *
     * @param Event $event event
     * @return void
     */
    public function onEntryChanged($event)
    {
        $this->clear();
    }

    /**
     * {@inheritDoc}
     */
    public function clear($id = null)
    {
        Cache::clearGroup('entries', 'entries');
    }
}
