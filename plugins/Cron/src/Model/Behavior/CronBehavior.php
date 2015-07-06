<?php

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
    public function initialize(array $config)
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
