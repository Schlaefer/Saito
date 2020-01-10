<?php

declare(strict_types=1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

use Cake\Routing\Router;

Router::connect(
    '/help/:id',
    [
        'plugin' => 'SaitoHelp',
        'controller' => 'SaitoHelps',
        'action' => 'languageRedirect',
    ],
    ['pass' => ['id']]
);

Router::connect(
    '/help/:lang/:id',
    [
        'plugin' => 'SaitoHelp',
        'controller' => 'SaitoHelps',
        'action' => 'view',
    ],
    ['pass' => ['lang', 'id']]
);
