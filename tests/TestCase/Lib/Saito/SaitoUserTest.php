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
    }

    public function testGetRole()
    {
        $user = [
            'id' => '2',
            'user_type' => 'The Man In Charge',
        ];
        $this->SaitoUser->setSettings($user);
        $this->assertEquals('The Man In Charge', $this->SaitoUser->getRole());
    }

    public function testIsUser()
    {
        $current = ['id' => 2];
        $this->SaitoUser->setSettings($current);

        //# test
        $tests = [
            ['in' => 1, 'expected' => false],
            ['in' => 2, 'expected' => true],
            ['in' => '1', 'expected' => false],
            ['in' => '2', 'expected' => true],
        ];
        foreach ($tests as $test) {
            $result = $this->SaitoUser->isUser(new SaitoUser(['id' => $test['in']]));
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

    public function setUp(): void
    {
        parent::setUp();
        $this->SaitoUser = new SaitoUser();
    }

    public function tearDown(): void
    {
        parent::tearDown();
        unset($this->SaitoUser);
    }
}
