<?php

declare(strict_types = 1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers 2018
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

use Cake\Cache\Cache;

Cache::setConfig(
    'uploadsThumbnails',
    [
        'className' => 'File',
        'prefix' => 'saito_thumbnails-',
        'path' => CACHE,
        'groups' => ['uploads'],
        'duration' => '+1 year'
    ]
);
