<?php

declare(strict_types = 1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers 2018
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace ImageUploader\Lib;

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
     * Render link for menu nav bar.
     *
     * @param array $eventData event-data
     * @return array event response
     */
    public function onRender(array $eventData)
    {
        $View = $eventData['View'];
        $title = $View->Layout->textWithIcon(h(__('Upload')), 'upload');

        return ['title' => $title, 'url' => '/users/uploads'];
    }
}
