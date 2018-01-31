<?php

namespace Saito\Test;

use Saito\User\SaitoUser;

class SaitoUserTest extends SaitoTestCase
{

    public function testGetSettings()
    {
        //# initialize with real user
        $user = [
            'id' => '1',
            'username' => 'Bob',
            'password' => 'foo',
        ];
        $this->SaitoUser->setSettings($user);
        $result = $this->SaitoUser->getSettings();
        $this->assertEquals($user, $result);

        //# initialize with real user
        $user = null;
        $this->SaitoUser->setSettings($user);
        $result = $this->SaitoUser->getSettings();
        $this->assertFalse(empty($result) === false);
    }

    public function testGetId()
    {
        //# initialize with real user
        $user = [
            'id' => '2',
        ];
        $this->SaitoUser->setSettings($user);
        $result = $this->SaitoUser->getId();
        $this->assertEquals(2, $result);

        //# initialize with empty
        $user = null;
        $this->SaitoUser->setSettings($user);
        $result = $this->SaitoUser->getId();
        $this->assertTrue(empty($result) === true);

        $user = false;
        $this->SaitoUser->setSettings($user);
        $result = $this->SaitoUser->getId();
        $this->assertTrue(empty($result) === true);

        $user = '';
        $this->SaitoUser->setSettings($user);
        $result = $this->SaitoUser->getId();
        $this->assertTrue(empty($result) === true);
    }

    public function testIsLoggedIn()
    {
        //# initialize with real user
        $user = [
            'id' => '2',
        ];
        $this->SaitoUser->setSettings($user);
        $result = $this->SaitoUser->isLoggedIn();
        $this->assertTrue($result);

        //# initialize with empty
        $user = null;
        $this->SaitoUser->setSettings($user);
        $result = $this->SaitoUser->isLoggedIn();
        $this->assertFalse($result);

        $user = false;
        $this->SaitoUser->setSettings($user);
        $result = $this->SaitoUser->isLoggedIn();
        $this->assertFalse($result);

        $user = '';
        $this->SaitoUser->setSettings($user);
        $result = $this->SaitoUser->isLoggedIn();
        $this->assertFalse($result);
    }

    public function testIsLoggedInUserIdIsMissing()
    {
        // missing 'id' key
        $user = ['username' => 'foo'];
        $this->SaitoUser->setSettings($user);
        $this->assertFalse($this->SaitoUser->isLoggedIn());
    }

    public function testIsLoggedInUserIdIsZero()
    {
        $user = ['id' => 0];
        $this->SaitoUser->setSettings($user);
        $this->assertFalse($this->SaitoUser->isLoggedIn());
    }

    public function testIsLoggedInUserIdIsStringZero()
    {
        $user = ['id' => '0'];
        $this->SaitoUser->setSettings($user);
        $this->assertFalse($this->SaitoUser->isLoggedIn());
    }

    public function testGetRole()
    {
        //# anon
        $user = null;
        $this->SaitoUser->setSettings($user);
        $result = $this->SaitoUser->getRole();
        $this->assertEquals('anon', $result);

        //# user
        $user = [
            'id' => '2',
            'user_type' => 'user',
        ];
        $this->SaitoUser->setSettings($user);
        $result = $this->SaitoUser->getRole();
        $this->assertEquals('user', $result);

        //# initialize with real user
        $user = [
            'id' => '2',
            'user_type' => 'mod',
        ];
        $this->SaitoUser->setSettings($user);
        $result = $this->SaitoUser->getRole();
        $this->assertEquals('mod', $result);

        //# admin
        $user = [
            'id' => '2',
            'user_type' => 'admin',
        ];
        $this->SaitoUser->setSettings($user);
        $result = $this->SaitoUser->getRole();
        $this->assertEquals('admin', $result);
    }

    public function testIsUser()
    {
        $current = ['id' => 2];
        $this->SaitoUser->setSettings($current);

        //# test
        $false = new SaitoUser(['id' => 1]);
        $true = new SaitoUser(['id' => 2]);
        $tests = [
            ['in' => 1, 'expected' => false],
            ['in' => 2, 'expected' => true],
            ['in' => '1', 'expected' => false],
            ['in' => '2', 'expected' => true],
            ['in' => $false, 'expected' => false],
            ['in' => $true, 'expected' => true],
        ];
        foreach ($tests as $test) {
            $result = $this->SaitoUser->isUser($test['in']);
            $this->assertEquals($test['expected'], $result);
        }
    }

    public function testSetter()
    {
        $user = ['id' => '2', 'user_type' => 'user'];
        $this->SaitoUser->setSettings($user);
        $this->SaitoUser->set('foo', 'bar');
        $this->assertEquals($this->SaitoUser->get('foo'), 'bar');
    }

    public function setUp()
    {
        parent::setUp();
        $this->SaitoUser = new SaitoUser();
    }

    public function tearDown()
    {
        parent::tearDown();
        unset($this->SaitoUser);
    }
}
