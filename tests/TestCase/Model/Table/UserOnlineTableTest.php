<?php

namespace App\Test\TestCase\Model\Table;

use Cake\Utility\Hash;
use Saito\Test\Model\Table\SaitoTableTestCase;

class UserOnlineTableTest extends SaitoTableTestCase
{

    public $tableClass = 'UserOnline';

    public $fixtures = [
        'app.category',
        'app.entry',
        'app.upload',
        'app.user',
        'app.user_online'
    ];

    protected $_fields = [
        'logged_in',
        'time',
        'user_id',
        'uuid'
    ];

    public function testSetOnlineArgumentOneInvalid()
    {
        $this->setExpectedException('\InvalidArgumentException');
        $this->Table->setOnline('', false);
    }

    public function testSetOnlineArgumentTwoInvalid()
    {
        $this->setExpectedException('\InvalidArgumentException');
        $this->Table->setOnline(1, 'a');
    }

    public function testSetOnlineSuccess()
    {
        //= insert registered user
        $_userId = 5;
        $this->_startUsersOnline[0] = [
            'uuid' => '5',
            'user_id' => 5,
            'time' => (string)time(),
            'logged_in' => true
        ];
        $this->Table->setOnline($_userId, true);

        $result = $this->Table->find()
            ->select($this->_fields)
            ->hydrate(false)
            ->all()
            ->toArray();

        $this->_assertTimeIsNow($result[0]);

        $expected = $this->_startUsersOnline;
        unset($expected[0]['time']);
        $this->assertEquals($result, $expected);

        //= insert anonymous user
        session_id('sessionIdTest');
        $_userId = session_id();
        $this->_startUsersOnline[1] = [
            'uuid' => substr(($_userId), 0, 32),
            'user_id' => null,
            'time' => (string)time(),
            'logged_in' => 0
        ];
        $this->Table->setOnline($_userId, false);

        $result = $this->Table->find()
            ->select($this->_fields)
            ->hydrate(false)
            ->all()
            ->toArray();
        $this->_assertTimeIsNow($result[1]);
        $result = Hash::remove($result, '{n}.time');
        $expected = Hash::remove($this->_startUsersOnline, '{n}.time');
        $this->assertEquals($result, $expected);

        //=	 *** Second 1 ***
        sleep(1);

        //= update registered user before time
        $_userId = 5;
        $this->Table->setOnline($_userId, true);

        $result = $this->Table->find()
            ->select($this->_fields)
            ->hydrate(false)
            ->all()
            ->toArray();
        $result = Hash::remove($result, '{n}.time');
        $expected = Hash::remove($this->_startUsersOnline, '{n}.time');
        $this->assertEquals($result, $expected);

        //= update anonymous user before time
        session_id('sessionIdTest');
        $_userId = session_id();
        $this->Table->setOnline($_userId, false);

        $result = $this->Table->find()
            ->select($this->_fields)
            ->hydrate(false)
            ->all()
            ->toArray();
        $result = Hash::remove($result, '{n}.time');
        $expected = Hash::remove($this->_startUsersOnline, '{n}.time');
        $this->assertEquals($result, $expected);

        //=	 *** Second 2 ***
        sleep(1);

        //= update anonymous user after time
        $this->Table->timeUntilOffline = 1;
        session_id('sessionIdTest');
        $_userId = session_id();
        $this->_startUsersOnline = [];
        $this->_startUsersOnline[0] = [
            'uuid' => substr(($_userId), 0, 32),
            'user_id' => null,
            'logged_in' => false
        ];
        $this->Table->setOnline($_userId, false);

        $result = $this->Table->find()
            ->select($this->_fields)
            ->hydrate(false)
            ->all()
            ->toArray();

        $this->_assertTimeIsNow($result[0]);

        $expected = $this->_startUsersOnline;
        $this->assertEquals($result, $expected);
    }

    public function testSetOffline()
    {
        //= insert new user
        $_userId = 5;
        $this->_startUsersOnline[0] = [
            'uuid' => '5',
            'user_id' => 5,
            'logged_in' => 1
        ];
        $this->Table->setOnline($_userId, true);

        //* test if user is inserted
        $result = $this->Table->find()
            ->select($this->_fields)
            ->hydrate(false)
            ->all()
            ->toArray();
        $expected = $this->_startUsersOnline;

        $time = $result[0]['time'];
        $this->assertGreaterThan(time() - 5, $time);
        unset($result[0]['time'], $time);

        $this->assertEquals($result, $expected);

        //= try to delete new user
        $this->Table->setOffline($_userId);
        $result = $this->Table->find()
            ->select($this->_fields)
            ->hydrate(false)
            ->all()
            ->toArray();
        $expected = [];
        $this->assertEquals($result, $expected);
    }

    public function testDeleteOutdated()
    {
        $this->Table->timeUntilOffline = 1;

        //= test remove outdated
        $_userId = 5;
        $this->Table->setOnline($_userId, true);
        sleep(2);
        $_userId = 6;
        $this->_startUsersOnline[] = [
            'uuid' => '6',
            'user_id' => 6,
            'time' => time(),
            'logged_in' => true
        ];
        $this->Table->setOnline($_userId, true);

        $result = $this->Table->find()
            ->select($this->_fields)
            ->hydrate(false)
            ->all()
            ->toArray();

        $this->_assertTimeIsNow($result[0]);

        $expected = $this->_startUsersOnline;
        unset(
            $expected[0]['time'],
            $result[0]['time']
        );
        $this->assertEquals($result, $expected);
    }

    public function testGetLoggedIn()
    {
        /*
         * test empty results, no user is logged in
         */
        $result = $this->Table->getLoggedIn();
        $expected = [];
        $this->assertEquals($result->count(), 0);

        /*
         * test
         */
        // login one user
        $_userId = 3;
        $this->Table->setOnline($_userId, true);

        session_id('sessionIdTest');
        $_userId = session_id();
        $this->Table->setOnline($_userId, false);

        $result = $this->Table->getLoggedIn()
            ->hydrate(false)
            ->toArray();
        $expected[] = [
            'id' => 1,
            'user' => [
                'id' => 3,
                'username' => 'Ulysses',
                'user_type' => 'user'
            ]
        ];
        $this->assertEquals($result, $expected);
    }

    protected function _assertTimeIsNow(&$UserOnline)
    {
        $this->assertWithinRange($UserOnline['time'], time(), 1);
        unset($UserOnline['time']);
    }

    public function setUp()
    {
        parent::setUp();
        $this->_startUsersOnline = [];
    }

    public function tearDown()
    {
        unset($this->UserOnline);
        parent::tearDown();
    }
}
