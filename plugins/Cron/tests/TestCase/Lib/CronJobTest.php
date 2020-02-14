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

use Cake\TestSuite\TestCase;
use Cron\Lib\CronJob;

class CronJobTest extends TestCase
{
    public function testAll()
    {
        $mock = $this->getMockBuilder('stdClass')
            ->setMethods(['callback'])
            ->getMock();
        $mock->expects($this->once())->method('callback');

        $due = '+1 day';
        $job = new CronJob('foo', $due, [$mock, 'callback']);

        $this->assertEquals('foo', $job->getUid());
        $due = new \DateTimeImmutable($due);
        $this->assertWithinRange($due->getTimestamp(), $job->getDue(), 2);

        $job->execute();
    }

    public function testConstructorWrongDue()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionCode(1571567221);
        new CronJob('foo', 'bar', [$this, '__construct']);
    }
}
