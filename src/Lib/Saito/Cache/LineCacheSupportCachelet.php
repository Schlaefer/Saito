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

use Cake\Event\Event;
use Cake\Event\EventListenerInterface;
use Cake\Event\EventManager;

class LineCacheSupportCachelet extends CacheSupportCachelet implements
    EventListenerInterface
{
    protected $_title = 'LineCache';

    protected $_LineCache;

    /**
     * Constructor
     *
     * @param \Saito\Cache\ItemCache $LineCache line-cache
     */
    public function __construct(ItemCache $LineCache)
    {
        EventManager::instance()->on($this);
        $this->_LineCache = $LineCache;
    }

    /**
     * {@inheritDoc}
     */
    public function implementedEvents(): array
    {
        return [
            'Model.Entry.update' => 'onEntryChanged',
        ];
    }

    /**
     * Event listener
     *
     * @param \Cake\Event\Event $event event
     * @return void
     * @throws \InvalidArgumentException
     */
    public function onEntryChanged(Event $event)
    {
        $posting = $event->getData('data');
        $tid = $posting->get('tid');
        if (!$tid) {
            throw new \InvalidArgumentException('No thread-id in event data.');
        }
        $id = $posting->get('id');
        $this->clear($id);
    }

    /**
     * {@inheritDoc}
     */
    public function clear($id = null)
    {
        if ($id === null) {
            $this->_LineCache->reset();
        } else {
            $this->_LineCache->delete($id);
        }
    }
}
