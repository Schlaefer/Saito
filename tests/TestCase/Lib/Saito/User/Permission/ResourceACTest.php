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

use Exception;
use Saito\User\Permission\ResourceAC;
use Saito\User\Permission\ResourceAI;
use Saito\User\SaitoUser;

class ResourceACTest extends SaitoTestCase
{
    public $fixtures = [
        'app.Category',
    ];

    /**
     * @var ResourceAC
     */
    private $ac;

    /**
     * @var ResourceAI
     */
    private $ai;

    public function setUp(): void
    {
        parent::setUp();
        $this->ac = new ResourceAC();
        $this->ai = new ResourceAI();
    }

    public function tearDown(): void
    {
        parent::tearDown();
        unset($this->ac, $this->ai);
    }

    public function testCheckEmpty()
    {
        $this->assertFalse($this->ac->check($this->ai));
    }

    public function testCheckAsRole()
    {
        $this->ac->asRole('mod');

        $user = new SaitoUser(['id' => 3, 'user_type' => 'user']);
        $this->assertFalse($this->ac->check($this->ai));

        $user = new SaitoUser(['id' => 3, 'user_type' => 'user']);
        $this->assertFalse($this->ac->check($this->ai->asUser($user)));

        $user = new SaitoUser(['id' => 2, 'user_type' => 'mod']);
        $this->assertTrue($this->ac->check($this->ai->asUser($user)));

        $this->ac->onRole('user');
        $this->assertFalse($this->ac->check($this->ai->asUser($user)->onRole('mod')));
        $this->assertTrue($this->ac->check($this->ai->asUser($user)->onRole('user')));
    }

    public function testCheckAsRoles()
    {
        $ac = $this->getMockBuilder(ResourceAC::class)
            ->setMethods(['onRole'])
            ->getMock();
        $ac->expects($this->at(0))
            ->method('onRole')
            ->with('user');
        $ac->expects($this->at(1))
            ->method('onRole')
            ->with('mod');

        $ac->onRoles('user', 'mod');
    }

    public function testCheckAsEverbody()
    {
        $this->assertFalse($this->ac->check($this->ai));

        $this->ac->asEverybody();
        $this->assertTrue($this->ac->check($this->ai));

        $this->ac->onRole('user');
        $this->assertFalse($this->ac->check($this->ai->onRole('mod')));
        $this->assertTrue($this->ac->check($this->ai->onRole('user')));
    }

    public function testCheckOnOwn()
    {
        $user = new SaitoUser(['id' => 2, 'user_type' => 'user']);
        $this->assertFalse($this->ac->check($this->ai->asUser($user)->onOwner(3)));

        $this->ac->onOwn();

        $user = new SaitoUser(['id' => 3, 'user_type' => 'user']);
        $this->assertTrue($this->ac->check($this->ai->asUser($user)->onOwner(3)));
    }

    public function testLock()
    {
        $methods = ['asRole' => 'a', 'asEverybody' => null, 'onOwn' => 1, 'onRole' => 'a'];
        foreach ($methods as $method => $arg) {
            try {
                (new ResourceAC())
                    ->lock()
                    ->{$method}($arg);
            } catch (Exception $e) {
                $this->assertEquals(1573820147, $e->getCode());
            }
        }
    }
}
