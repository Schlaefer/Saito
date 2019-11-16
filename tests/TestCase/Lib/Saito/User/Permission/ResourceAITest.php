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
use Saito\User\Permission\ResourceAI;
use Saito\User\SaitoUser;

class ResourceAITest extends TestCase
{
    public function testSetterAndGetter()
    {
        $identifier = new ResourceAI();

        $this->assertNull($identifier->getRole());
        $this->assertNull($identifier->getOwner());
        $this->assertNull($identifier->getUser());

        $user = new SaitoUser();
        $identifier
            ->asUser($user)
            ->onRole('admin')
            ->onOwner(5);

        $this->assertSame('admin', $identifier->getRole());
        $this->assertSame(5, $identifier->getOwner());
        $this->assertSame($user, $identifier->getUser());
    }
}
