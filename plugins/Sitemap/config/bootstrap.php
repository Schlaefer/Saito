<?php
declare(strict_types=1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

use Saito\Event\SaitoEventManager;

/**
 * register plugin's admin-UI in admin-backend
 */
SaitoEventManager::getInstance()->attach(
    'Request.Saito.View.Admin.plugins',
    function () {
        $url = '/admin/plugins/sitemap';
        $title = 'Sitemap';

        return compact('url', 'title');
    }
);
