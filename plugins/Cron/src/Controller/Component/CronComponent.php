<?php

namespace Cron\Controller\Component;

use Cake\Controller\Component;
use Cake\Event\Event;
use Saito\App\Registry;

class CronComponent extends Component
{

    public function shutdown(Event $event)
    {
        $this->execute();
    }

    public function __call($method, $params)
    {
        $Cron = Registry::get('Cron');
        $proxy = [$Cron, $method];
        if (is_callable($proxy)) {
            return call_user_func_array($proxy, $params);
        }
    }

}
