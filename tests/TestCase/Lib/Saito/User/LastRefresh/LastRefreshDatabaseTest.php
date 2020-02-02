<?php

namespace Saito\Test\User\LastRefresh;

use Saito\Test\SaitoTestCase;
use Saito\User\CurrentUser\CurrentUserFactory;

class LastRefreshDatabaseTest extends SaitoTestCase
{
    public $fixtures = [
        'app.User',
        'app.UserRead',
    ];

    /**
     * @var CurrentUserInterface;
     */
    public $CurrentUser;

    public function setUp(): void
    {
        parent::setUp();

        $this->CurrentUser = CurrentUserFactory::createLoggedIn(['id' => 1]);
        $this->LastRefresh = $this->CurrentUser->getLastRefresh();
    }

    public function tearDown(): void
    {
        unset($this->CurrentUser);
        unset($this->LastRefresh);
        parent::tearDown();
    }

    /**
     * Tests that a newly registered users sees everything as new
     */
    public function testIsNewerThanForNewUsers()
    {
        $userData = ['id' => 1, 'last_refresh' => null];
        $this->CurrentUser->setSettings($userData);

        $this->assertNull($this->LastRefresh->isNewerThan(time()));
        $this->assertNull(
            $this->LastRefresh->isNewerThan(
                date(
                    'Y-m-d h:i:s',
                    time()
                )
            )
        );
    }

    /**
     * tests entry is newer than last refresh
     */
    public function testIsNewerThanTrue()
    {
        $time = time();
        $lastRefresh = bDate($time + 10);
        $userData = ['id' => 1, 'last_refresh' => $lastRefresh];
        $this->CurrentUser->setSettings($userData);

        $this->assertTrue($this->LastRefresh->isNewerThan($time));
        $this->assertTrue($this->LastRefresh->isNewerThan(bDate($time)));
    }

    /**
     * tests entry is older than last refresh
     */
    public function testIsNewerThanFalse()
    {
        $time = time();
        $lastRefresh = bDate($time - 10);
        $userData = ['id' => 1, 'last_refresh' => $lastRefresh];
        $this->CurrentUser->setSettings($userData);

        $this->assertFalse($this->LastRefresh->isNewerThan($time));
        $this->assertFalse($this->LastRefresh->isNewerThan(bDAte($time)));
    }
}
