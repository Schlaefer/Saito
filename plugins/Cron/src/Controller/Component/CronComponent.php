<?php

declare(strict_types=1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

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
