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
            'Request.Saito.View.MainMenu.navItem' => 'onRender'
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
        $View = $eventData['View'];
        $title = $View->Layout->textWithIcon(h(__('Bookmarks')), 'bookmark');

        // @td i18n
        return ['title' => $title, 'url' => 'bookmarks'];
    }
}
