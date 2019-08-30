<?php

declare(strict_types=1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace App\Test\TestCase\Model\Table;

use Cake\Utility\Hash;
use Saito\Test\Model\Table\SaitoTableTestCase;

class UserOnlineTableTest extends SaitoTableTestCase
{

    public $tableClass = 'UserOnline';

    public $fixtures = [
        'app.Category',
        'app.Entry',
        'app.User',
        'app.UserOnline'
    ];

    protected $_fields = [
        'logged_in',
        'time',
        'user_id',
        'uuid'
    ];

    public function testSetOnlineSuccess()
    {
        /// set logged-in user 5 online
        $_userId = 5;
        $this->_startUsersOnline[0] = [
            'uuid' => '5',
            'user_id' => 5,
            'time' => (string)time(),
            'logged_in' => true
        ];
        $this->Table->setOnline((string)$_userId, true);

        $result = $this->Table->find()
            ->select($this->_fields)
            ->enableHydration(false)
            ->all()
            ->toArray();

        $timeTmpUser = $result[0]['time'];
        $this->_assertTimeIsNow($result[0]);

        $expected = $this->_startUsersOnline;
        unset($expected[0]['time']);
        $this->assertEquals($result, $expected);

        /// set anon user online
        $_userId = 'sessionIdTest';
        $this->_startUsersOnline[1] = [
            'uuid' => substr(($_userId), 0, 32),
            'user_id' => null,
            'time' => (string)time(),
            'logged_in' => 0
        ];
        $this->Table->setOnline((string)$_userId, false);

        $result = $this->Table->find()
            ->select($this->_fields)
            ->enableHydration(false)
            ->all()
            ->toArray();
        $timeTmpAnon = $result[1]['time'];
        $this->_assertTimeIsNow($result[1]);
        $result = Hash::remove($result, '{n}.time');
        $expected = Hash::remove($this->_startUsersOnline, '{n}.time');
        $this->assertEquals($result, $expected);

        //// *** Second 1 *** - Table should not change.
        sleep(1);

        /// Update registered user before time.
        $_userId = 5;
        $this->Table->setOnline((string)$_userId, true);

        $result = $this->Table->find()
            ->select($this->_fields)
            ->enableHydration(false)
            ->all()
            ->toArray();
        $this->assertEquals($timeTmpUser, $result[0]['time']);
        $this->assertEquals($timeTmpAnon, $result[1]['time']);
        $result = Hash::remove($result, '{n}.time');
        $expected = Hash::remove($this->_startUsersOnline, '{n}.time');
        $this->assertEquals($result, $expected);

        /// Update anonymous user before time.
        $_userId = 'sessionIdTest';
        $this->Table->setOnline((string)$_userId, false);

        $result = $this->Table->find()
            ->select($this->_fields)
            ->enableHydration(false)
            ->all()
            ->toArray();
        $this->assertEquals($timeTmpUser, $result[0]['time']);
        $this->assertEquals($timeTmpAnon, $result[1]['time']);
        $result = Hash::remove($result, '{n}.time');
        $expected = Hash::remove($this->_startUsersOnline, '{n}.time');
        $this->assertEquals($result, $expected);

        //// *** Second 2 *** - Forces an table update.
        sleep(1);
        $this->Table->timeUntilOffline = 1;
        $this->Table->gc();

        /// update anonymous user after time
        $_userId = 'sessionIdTest';
        $this->_startUsersOnline = [];
        $this->_startUsersOnline[0] = [
            'uuid' => substr(($_userId), 0, 32),
            'user_id' => null,
            'logged_in' => false
        ];
        $this->Table->setOnline((string)$_userId, false);

        $result = $this->Table->find()
            ->select($this->_fields)
            ->enableHydration(false)
            ->all()
            ->toArray();

        $this->assertNotEquals($timeTmpAnon, $result[0]['time']);
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
        $this->Table->setOnline((string)$_userId, true);

        //* test if user is inserted
        $result = $this->Table->find()
            ->select($this->_fields)
            ->enableHydration(false)
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
            ->enableHydration(false)
            ->all()
            ->toArray();
        $expected = [];
        $this->assertEquals($result, $expected);
    }

    public function testDeleteOutdated()
    {
        $this->Table->timeUntilOffline = 1;

        /// add new user
        $_userId = 5;
        $this->Table->setOnline((string)$_userId, true);

        /// wait
        sleep(2);
        $this->Table->gc();

        /// add another user after gc time, previous user should be gone
        $_userId = 6;
        $this->_startUsersOnline[] = [
            'uuid' => '6',
            'user_id' => 6,
            'time' => time(),
            'logged_in' => true
        ];
        $this->Table->setOnline((string)$_userId, true);

        $result = $this->Table->find()
            ->select($this->_fields)
            ->enableHydration(false)
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
        $this->Table->setOnline((string)$_userId, true);

        $_userId = 'sessionIdTest';
        $this->Table->setOnline((string)$_userId, false);

        $result = $this->Table->getLoggedIn()
            ->enableHydration(false)
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
        $time = time();
        $this->assertWithinRange(
            $UserOnline['time'],
            $time,
            1,
            sprintf('Time %s was not fuzzy within %s.', $UserOnline['time'], time())
        );
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
