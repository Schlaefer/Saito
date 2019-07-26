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

// threads collection
// -------------------------------------

Router::scope(
    '/api/v2/',
    ['plugin' => 'Api'],
    function ($routes) {
    }
);
