<?php
declare(strict_types=1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace Cron\Model\Behavior;

use Cake\ORM\Behavior;
use Saito\App\Registry;

class CronBehavior extends Behavior
{
    /**
     * {@inheritDoc}
     *
     * Register table cron-jobs as behavior config.
     */
    public function initialize(array $config): void
    {
        $cron = Registry::get('Cron');
        foreach ($config as $func => $options) {
            $cron->addCronJob(
                $options['id'],
                $options['due'],
                [$this->_table, $func]
            );
        }
    }
}
