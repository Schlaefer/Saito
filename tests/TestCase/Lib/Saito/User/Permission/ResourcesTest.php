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
use Saito\User\Permission\Resources;

class ResourcesTest extends TestCase
{
    public function testAddAndGetAndClone()
    {
        $name = 'foo';
        $resource = new Resource($name);

        $resources = new Resources();
        $resources->add($resource);

        $this->assertSame($resource, $resources->get($name));

        $clone = clone $resources;
        $this->assertNotSame($resource, $clone->get($name));
    }
}
