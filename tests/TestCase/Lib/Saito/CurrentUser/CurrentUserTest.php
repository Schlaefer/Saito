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

use Saito\User\CurrentUser\CurrentUser;

class CurrentUserTest extends SaitoTestCase
{
    public function testIsLoggedIn()
    {
        //# initialize with real user
        $user = [
            'id' => '2',
        ];
        $this->CurrentUser->setSettings($user);
        $result = $this->CurrentUser->isLoggedIn();
        $this->assertTrue($result);

        //# initialize with empty
        $user = [];
        $this->CurrentUser->setSettings($user);
        $result = $this->CurrentUser->isLoggedIn();
        $this->assertFalse($result);
    }

    public function testIsLoggedInUserIdIsMissing()
    {
        // missing 'id' key
        $user = ['username' => 'foo'];
        $this->CurrentUser->setSettings($user);
        $this->assertFalse($this->CurrentUser->isLoggedIn());
    }

    public function testIsLoggedInUserIdIsZero()
    {
        $user = ['id' => 0];
        $this->CurrentUser->setSettings($user);
        $this->assertFalse($this->CurrentUser->isLoggedIn());
    }

    public function testIsLoggedInUserIdIsStringZero()
    {
        $user = ['id' => '0'];
        $this->CurrentUser->setSettings($user);
        $this->assertFalse($this->CurrentUser->isLoggedIn());
    }

    public function setUp(): void
    {
        parent::setUp();
        $this->CurrentUser = new CurrentUser();
    }

    public function tearDown(): void
    {
        parent::tearDown();
        unset($this->CurrentUser);
    }
}
