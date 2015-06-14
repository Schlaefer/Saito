<?php

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
