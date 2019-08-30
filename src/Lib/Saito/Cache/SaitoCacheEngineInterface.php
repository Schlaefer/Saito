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

interface SaitoCacheEngineInterface
{

    /**
     * read
     *
     * @param string $name name
     * @return mixed
     */
    public function read($name);

    /**
     * Write
     *
     * @param string $name name
     * @param mixed $content content
     * @return void
     */
    public function write($name, $content);
}
