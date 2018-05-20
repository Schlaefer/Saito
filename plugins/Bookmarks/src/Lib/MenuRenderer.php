<?php

namespace Bookmarks\Lib;

use Saito\Event\SaitoEventListener;

class MenuRenderer implements SaitoEventListener
{

    /**
     * {@inheritdoc}
     *
     * @return array events
     */
    public function implementedSaitoEvents()
    {
        return [
            'Request.Saito.View.UserMenu.navItem' => 'onRender'
        ];
    }

    /**
     * Render bookmarks link for menu nav bar.
     *
     * @param array $eventData event-data
     * @return array event response
     */
    public function onRender(array $eventData)
    {
        return ['title' => __('Bookmarks'), 'url' => '/bookmarks', 'icon' => 'bookmark'];
    }
}
