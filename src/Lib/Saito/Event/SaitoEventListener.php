<?php

declare(strict_types=1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace Saito\Event;

interface SaitoEventListener
{

    /**
     * Get implemented Saito-events.
     *
     * @return array events
     */
    public function implementedSaitoEvents();
}
