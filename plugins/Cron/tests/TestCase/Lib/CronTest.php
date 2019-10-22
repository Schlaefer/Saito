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
    public function testSimpleCronJobRunEmptyPersistance()
    {
        $cron = new Cron();
        $mock = $this->getMockBuilder('stdClass')
            ->setMethods(['callback'])
            ->getMock();
        $mock->expects($this->once())->method('callback');
        $cron->addCronJob('foo', '+1 day', [$mock, 'callback']);

        $cron->execute();
    }

    public function testDueIsReadUpdatedAndWritten()
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

    public function testGc()
    {
        $lastRuns = ['pastNotRun' => time() - 3, 'futureNotRun' => time() + 3];
        Cache::write('Plugin.Cron.lastRuns', $lastRuns, 'long');

        $cron = new Cron();
        $cron->execute();

        $result = Cache::read('Plugin.Cron.lastRuns', 'long');
        $this->assertArrayNotHasKey('pastNotRun', $result);
        $this->assertArrayHasKey('futureNotRun', $result);
    }
}
