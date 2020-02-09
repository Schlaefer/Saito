<?php
declare(strict_types=1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace Saito\Test;

use PHPUnit\Framework\TestCase;
use Saito\User\Permission\Resource;
use Saito\User\Permission\ResourceAC;
use Saito\User\Permission\ResourceAI;

class ResourceTest extends TestCase
{
    public function testDisallow()
    {
        $resource = new Resource('foo');
        $disallowed = $this->getMockBuilder(ResourceAC::class)
            ->setMethods(['check'])
            ->getMock();
        $allowed = $this->getMockBuilder(ResourceAC::class)
            ->setMethods(['check'])
            ->getMock();

        $disallowed->expects($this->once())->method('check')->willReturn(true);
        $allowed->expects($this->never())->method('check');

        $resource->allow($allowed);
        $resource->disallow($disallowed);

        $ai = new ResourceAI();
        $resource->check($ai);
    }

    public function testAllowFalse()
    {
        $resource = new Resource('foo');
        $allowed1 = $this->getMockBuilder(ResourceAC::class)
            ->setMethods(['check'])
            ->getMock();

        $ai = new ResourceAI();
        $allowed1->expects($this->once())->method('check')->with($ai)->willReturn(false);
        $resource->allow($allowed1);

        $this->assertFalse($resource->check($ai));
    }

    public function testAllowTrue()
    {
        $resource = new Resource('foo');
        $allowed1 = $this->getMockBuilder(ResourceAC::class)
            ->setMethods(['check'])
            ->getMock();
        $allowed2 = $this->getMockBuilder(ResourceAC::class)
            ->setMethods(['check'])
            ->getMock();
        $allowed3 = $this->getMockBuilder(ResourceAC::class)
            ->setMethods(['check'])
            ->getMock();

        $ai = new ResourceAI();

        $allowed1->expects($this->once())->method('check')->with($ai)->willReturn(false);
        $allowed2->expects($this->once())->method('check')->with($ai)->willReturn(false);
        $allowed3->expects($this->once())->method('check')->with($ai)->willReturn(true);

        $resource
            ->allow($allowed1)
            ->allow($allowed2)
            ->allow($allowed3);

        $this->assertTrue($resource->check($ai));
    }
}
