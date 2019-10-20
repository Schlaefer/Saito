<?php

declare(strict_types=1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace Cron\Test;

use Cake\Cache\Cache;
use Cron\Lib\Cron;
use Saito\Test\SaitoTestCase;

class CronTest extends SaitoTestCase
{
    public function testSimpleCronJobRun()
    {
        $cron = new Cron();
        $mock = $this->getMockBuilder('stdClass')
            ->setMethods(['callback'])
            ->getMock();
        $mock->expects($this->exactly(2))->method('callback');
        $cron->addCronJob('foo', '+1 day', [$mock, 'callback']);

        $mock = $this->getMockBuilder('stdClass')
            ->setMethods(['callback'])
            ->getMock();
        $mock->expects($this->exactly(3))->method('callback');
        $cron->addCronJob('bar', '-1 day', [$mock, 'callback']);

        $cron->execute();
        $cron->execute();
        $cron->clearHistory();
        $cron->execute();
    }

    public function testDueIsUpdatedAndPersisted()
    {
        $cron = new Cron();

        $lastRuns = ['run' => time() - 3, 'notRun' => time() + 3];
        Cache::write('Plugin.Cron.lastRuns', $lastRuns, 'long');

        $run = $this->getMockBuilder('stdClass')
            ->setMethods(['callback'])
            ->getMock();
        $run->expects($this->exactly(1))->method('callback');
        $newDue = '+1 day';
        $cron->addCronJob('run', $newDue, [$run, 'callback']);

        $notRun = $this->getMockBuilder('stdClass')
            ->setMethods(['callback'])
            ->getMock();
        $notRun->expects($this->never())->method('callback');
        $cron->addCronJob('notRun', '+1 day', [$notRun, 'callback']);

        $cron->execute();

        $result = Cache::read('Plugin.Cron.lastRuns', 'long');
        $this->assertEquals($lastRuns['notRun'], $result['notRun']);
        $this->assertWithinRange(strtotime($newDue), $result['run'], 2);
    }
}
