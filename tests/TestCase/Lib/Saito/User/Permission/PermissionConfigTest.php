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

use Cake\TestSuite\TestCase;
use Saito\User\Permission\PermissionConfig;

class PermissionConfigTest extends TestCase
{
    public function testSetAllowAllOnlyOneActive()
    {
        $pc = new PermissionConfig();
        $resource = 'foo';

        $pc->allowAll($resource);
        $result = $pc->getForce($resource)->check($resource);
        $this->assertTrue($result);

        $pc->allowAll($resource, false);
        $result = $pc->getForce($resource)->check($resource);
        $this->assertFalse($result);
    }
}
