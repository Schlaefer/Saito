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
use Saito\User\Permission\Roles;

class RolesTest extends TestCase
{
    public function testRolesGet()
    {
        $roles = new Roles();
        $this->assertEquals([], $roles->get('foo'));

        $roles->add('anon', 0);
        $roles->add('sub', 1);
        $roles->add('foo', 2, ['anon', 'sub']);

        $this->assertEquals(['foo', 'anon', 'sub'], $roles->get('foo'));
        $this->assertEquals(['foo', 'sub'], $roles->get('foo', false));
        $this->assertEquals(['sub'], $roles->get('foo', false, false));
    }
}
