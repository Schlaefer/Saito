<?php

namespace Cron\Controller\Component;

use Cake\Controller\Component;
use Cake\Event\Event;
use Saito\App\Registry;

class CronComponent extends Component
{

    /**
     * {@inheritDoc}
     */
    public function shutdown(Event $event)
    {
        $Cron = Registry::get('Cron');
        $Cron->execute();
    }
}
