<?php

declare(strict_types=1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

use Cake\Cache\Cache;

if (Cache::getConfig('bbcodeParserEmbed') === null) {
    Cache::setConfig(
        'bbcodeParserEmbed',
        [
            'className' => 'File',
            'prefix' => 'saito_embed-',
            'path' => CACHE,
            'groups' => ['embed'],
            'duration' => '+1 month',
        ]
    );
}
