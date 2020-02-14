<?php
declare(strict_types=1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

use Cake\Routing\RouteBuilder;
use Cake\Routing\Router;

Router::plugin(
    'SaitoSearch',
    ['path' => '/searches'],
    function (RouteBuilder $routes) {
        $routes->connect('/:action', ['controller' => 'searches']);
    }
);
