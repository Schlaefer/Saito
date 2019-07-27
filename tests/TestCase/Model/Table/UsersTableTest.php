<?php

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\UsersTable;
use Cake\I18n\Time;
use Cake\ORM\TableRegistry;
use Saito\App\Registry;
use Saito\Test\Model\Table\SaitoTableTestCase;

class UsersTableTest extends SaitoTableTestCase
{

    public $tableClass = 'Users';

    public $fixtures = [
        'app.Category',
        'app.Entry',
        'app.Setting',
        'app.Smiley',
        'app.SmileyCode',
        'app.User',
        'app.UserBlock',
        'app.UserIgnore',
        'app.UserOnline',
        'app.UserRead',
        'plugin.Bookmarks.Bookmark',
        'plugin.ImageUploader.Uploads',
    ];

    public function testEmptyUserCategoryCustom()
    {
        $User = $this->Table->get(1);
        $result = $User->get('user_category_custom');
        $this->assertTrue(is_array($result));
        $this->assertEmpty($result);
    }

    public function testFindLatest()
    {
        $result = $this->Table->find('latest')->firstOrFail();
        $this->assertEquals($result->get('id'), 9);
    }

    public function testSetCategoryAll()
    {
        $userId = 1;
        $this->Table->setCategory($userId, 'all');
        $result = $this->Table->find()
            ->select('user_category_active')
            ->where(['id' => $userId])
            ->first();
        $this->assertEquals(-1, $result->get('user_category_active'));
    }

    /**
     * Set to a single category – Success
     */
    public function testSetCategorySingle()
    {
        $userId = 1;
        $this->Table->setCategory($userId, 5);
        $result = $this->Table->find()
            ->select('user_category_active')
            ->where(['id' => $userId])
            ->first();
        $this->assertEquals(5, $result->get('user_category_active'));
    }

    /**
     * Set to a single category – Failure because category does not exists
     */
    public function testSetCategorySingleNotExist()
    {
        $this->expectException('\InvalidArgumentException');
        $this->Table->setCategory(1, 'fwefwe');
    }

    /**
     * Set custom category set - Success
     */
    public function testSetCategoryCustom()
    {
        $userId = 1;

        $data = [
            '1' => '0',
            '2' => '1',
            '3' => '0',
            '5' => '0',
            '9999' => '1',
            ['foo']
        ];

        $expected = [
            '1' => false,
            '2' => true,
            '3' => false,
            '5' => false,
        ];

        $this->Table->setCategory($userId, $data);

        $User = $this->Table->find()
            ->select(['user_category_active', 'user_category_custom'])
            ->where(['id' => $userId])
            ->first();
        $this->assertEquals(0, $User->get('user_category_active'));
        $this->assertEquals($expected, $User->get('user_category_custom'));
    }

    /**
     * Set custom category set - Failure because no valid category is found
     */
    public function testSetCategoryCustomNotExist()
    {
        $this->expectException('InvalidArgumentException');
        $this->Table->setCategory(1, ['foo']);
    }

    public function testSetLastRefresh()
    {
        //= automatic timestamp
        $expected = new \DateTime();
        $userId = 3;
        $this->Table->setLastRefresh($userId);
        $result = $this->Table->get($userId)->get('last_refresh_tmp');
        $this->assertTrue($result->wasWithinLast('1 seconds'));

        //= with explicit timestamp
        $expected = (new \DateTime())->setTimestamp(1);
        $userId = 1;
        $this->Table->setLastRefresh($userId, $expected);
        $user = $this->Table->get($userId);
        $result = $user->get('last_refresh');
        $this->assertEquals($expected, $result);

        $result = $user->get('last_refresh_tmp');
        $this->assertTrue($result->wasWithinLast('1 seconds'));
    }

    public function testIncrementLogins()
    {
        $usersBeforeIncrements = $this->Table->find()->count();

        //= check setup
        $userId = 1;
        $expected = 0;
        $user = $this->Table->get($userId);
        $result = $user->get('logins');
        $this->assertEquals($expected, $result);

        //= increment one
        $expected = 1;
        $user = $this->Table->get($userId);
        $this->Table->incrementLogins($user);
        $result = $this->Table->get($userId)->get('logins');
        $this->assertEquals($expected, $result);

        $_lastLogin = new \DateTime($user->get('last_login'));
        $now = new \DateTime();
        /* on a decently performing server the timestamp is maybe not equal
         * but within one second time diff
         */
        $diff = $now->diff($_lastLogin);
        $result = (int)$diff->format("s");
        $expected = 1;
        $this->assertLessThanOrEqual($expected, $result);
        $this->assertGreaterThanOrEqual(0, $result);

        //= increment random amount
        $this->Table->incrementLogins($user, 15);
        $expected = 16;
        $result = $this->Table->get($userId)->get('logins');
        $this->assertEquals($expected, $result);

        $_lastLogin = new \DateTime($user->get('last_login'));
        $now = new \DateTime();
        /* on a decently performing server the timestamp is maybe not equal
         * but within one second time diff
         */
        $diff = $now->diff($_lastLogin);
        $result = (int)$diff->format("s");
        $expected = 1;
        $this->assertLessThanOrEqual($expected, $result);
        $this->assertGreaterThanOrEqual(0, $result);

        //* check we have no DB leaking
        $usersAfterIncrements = $this->Table->find()->count();
        $this->assertEquals($usersBeforeIncrements, $usersAfterIncrements);
    }

    public function testCountSolves()
    {
        $result = $this->Table->countSolved(3);
        $this->assertEquals($result, 1);
    }

    public function testDeleteUser()
    {
        $this->Table->UserIgnores = $this->getMockForModel(
            'UserIgnores',
            ['deleteUser'],
            [false, 'user_ignore', 'test']
        );
        $this->Table->UserIgnores->expects($this->once())
            ->method('deleteUser')
            ->with(3)
            ->will($this->returnValue(true));

        //
        $result = $this->Table->exists(3);
        $this->assertTrue($result);

        $Entries = TableRegistry::get('Entries');
        $entriesBeforeDelete = $Entries->find('all')->count();
        $this->assertGreaterThan(0, $entriesBeforeDelete);

        $Bookmarks = TableRegistry::get('Bookmarks');
        $allBookmarksBeforeDelete = $Bookmarks->find()->count();
        $userBookmarksBeforeDelete = $Bookmarks->findAllByUserId(3)->count();
        // user has bookmarks before the test
        $this->assertGreaterThan(0, $userBookmarksBeforeDelete);
        // other users have bookmarks
        $this->assertGreaterThan(
            $userBookmarksBeforeDelete,
            $allBookmarksBeforeDelete
        );
        // test uploads are deleted
        $this->assertGreaterThan(0, $this->Table->Uploads->findByUserId(3)->count());

        $this->Table->deleteAllExceptEntries(3);

        // user is deleted
        $result = $this->Table->exists(3);
        $this->assertFalse($result);

        // make sure we delete without cascading to associated models
        $expected = $entriesBeforeDelete;
        $result = $Entries->find('all')->count();
        $this->assertEquals($result, $expected);

        // delete associated bookmarks
        $userBookmarksAfterDelete = $Bookmarks->find('all')
            ->where(['user_id' => 3])
            ->count();
        $this->assertEquals($userBookmarksAfterDelete, 0);
        $allBookmarksAfterDelete = $Bookmarks->find('all')->count();
        $this->assertEquals(
            $allBookmarksBeforeDelete - $userBookmarksBeforeDelete,
            $allBookmarksAfterDelete
        );

        //// delete uploads
        // user uploads gone
        $this->assertEquals(0, $this->Table->Uploads->findByUserId(3)->count());
        // don't delete everything
        $this->assertGreaterThan(0, $this->Table->Uploads->find('all')->count());
    }

    public function testSetPassword()
    {
        $newPw = 'test1';

        $Entity = $this->Table->get(3);
        $Entity->set('password', $newPw);
        $this->Table->save($Entity);

        $Entity = $this->Table->get(3);
        $result = $this->Table->checkPassword($newPw, $Entity->get('password'));
        $this->assertTrue($result);
    }

    public function testSetUsername()
    {
        $Users = $this->getMockForTable('Users', ['_dispatchEvent']);
        $Entity = $Users->get(3);
        $Users->expects($this->once())
            ->method('_dispatchEvent')
            ->with('Cmd.Cache.clear', ['cache' => 'Thread']);
        $Users->patchEntity($Entity, ['username' => 'foo']);
        $Users->save($Entity);
    }

    public function testActivateIdNotInt()
    {
        $this->expectException('InvalidArgumentException');
        $this->Table->activate('stro', '123');
    }

    public function testActivateCodeNotString()
    {
        $this->expectException('InvalidArgumentException');
        $this->Table->activate(123, 123);
    }

    public function testActivateUserNotFound()
    {
        $this->expectException('InvalidArgumentException');
        $this->Table->activate(123, '123');
    }

    public function testActivateUserAlreadyActivated()
    {
        $result = $this->Table->activate(1, '123');
        $this->assertEquals('already', $result['status']);
    }

    public function testActivateUserWrongCode()
    {
        $result = $this->Table->activate(10, '123');
        $this->assertFalse($result);
    }

    public function testActivateUserSuccess()
    {
        $result = $this->Table->activate(10, '1548');
        $this->assertEquals('activated', $result['status']);
        $user = $this->Table->get(10);
        $this->assertEquals(0, $user->get('activate_code'));
        $this->assertEquals($user->toArray(), $result['User']->toArray());
    }

    public function testBeforeValidate()
    {
        $Entity = $this->Table->get(3);
        $Entity->set('user_forum_refresh_time', '1');
        $this->Table->save($Entity);

        $expected = 1;
        $result = $this->Table->get(3)->get('user_forum_refresh_time');
        $this->assertEquals($result, $expected);

        $Entity->set('user_forum_refresh_time', '');
        $this->Table->save($Entity);
        $expected = 0;
        $result = $this->Table->get(3)->get('user_forum_refresh_time');
        $this->assertEquals($result, $expected);
    }

    public function testValidateConfirmPassword()
    {
        $Entity = $this->Table->get(3);
        $data = [
            'password' => 'new_pw',
            'password_confirm' => 'new_pw_wrong'
        ];
        $this->Table->patchEntity($Entity, $data);
        $this->assertTrue(array_key_exists('password', $Entity->getErrors()));

        $Entity = $this->Table->get(3);
        $data = [
            'password' => 'new_pw',
            'password_confirm' => 'new_pw'
        ];
        $this->Table->patchEntity($Entity, $data);
        $this->assertEmpty($Entity->getErrors());
    }

    public function testValidateCheckOldPassword()
    {
        $Entity = $this->Table->get(3);
        $data = [
            'password_old' => 'something',
            'password' => 'new_pw_2',
            'password_confirm' => 'new_pw_2',
        ];
        $this->Table->patchEntity($Entity, $data);
        $this->assertTrue(array_key_exists('password_old', $Entity->getErrors()));

        $data = [
            'password_old' => 'test',
            'password' => 'new_pw_2',
            'password_confirm' => 'new_pw_2'
        ];
        $this->Table->patchEntity($Entity, $data);
        $this->assertFalse(array_key_exists('password_old', $Entity->getErrors()));
    }

    public function testAutoUpdatePassword()
    {
        // test exchanging
        $userId = 3;
        $newPassword = 'testtest';
        $this->Table->autoUpdatePassword($userId, $newPassword);
        $Entity = $this->Table->get($userId);
        $result = $this->Table->checkPassword(
            $newPassword,
            $Entity->get('password')
        );
        $this->assertTrue($result);

        // don't exchange if up to date
        $userId = 6;
        $newPassword = 'testtest';
        $oldPassword = $this->Table->get($userId)->get('password');
        $this->Table->autoUpdatePassword($userId, $newPassword);
        $result = $this->Table->get($userId)->get('password');
        $this->assertEquals($oldPassword, $result);
    }

    public function testRegisterGc()
    {
        // Configure::write('Saito.Settings.topics_per_page', 20);

        $_userCountBeforeAction = $this->Table->find()->count();

        $user1 = [
            'username' => 'Reginald',
            'password' => 'test',
            'password_confirm' => 'test',
            'user_email' => 'Reginald@example.com',
            'activate_code' => 5,
        ];
        $user2 = [
            'username' => 'Ronald',
            'password' => 'test',
            'password_confirm' => 'test',
            'user_email' => 'Ronald@example.com',
            'activate_code' => 539,
        ];
        $this->Table->register($user1);
        $this->Table->register($user2);

        $user = $this->Table->find()
            ->where(['username' => 'Ronald'])
            ->first();
        $user->set('registered', date('Y-m-d H:i:s', time() - 90000));
        $this->Table->save($user);

        $cron = Registry::get('Cron');
        $cron->clearHistory();
        $cron->execute();

        $result = $this->Table->exists(['username' => 'Reginald']);
        $this->assertTrue($result);

        $result = $this->Table->exists(['username' => 'Ronald']);
        $this->assertFalse($result);

        $_userCountAfterAction = $this->Table->find()->count();
        // (reginald stays) + (fixture user-id 9 is gone) = 0
        $this->assertEquals(
            $_userCountBeforeAction,
            $_userCountAfterAction - 0
        );
    }

    public function testRegisterSuccess()
    {
        // new user
        $pw = 'test';
        $data = [
            'username' => 'Reginald',
            'password' => $pw,
            'password_confirm' => $pw,
            'user_email' => 'Reginald@example.com',
        ];
        $now = time();
        $user = $this->Table->register($data);

        $this->assertEmpty($user->getErrors());

        $result = $this->Table->checkPassword($pw, $user->get('password'));
        $this->assertTrue($result);

        $expected = $data + [
                'registered' => new Time($now),
                'user_type' => 'user'
            ];
        unset($expected['password'], $expected['password_confirm']);
        $result = array_intersect_key(
            $user->toArray(),
            array_fill_keys(
                ['username', 'user_email', 'user_type', 'registered'],
                1
            )
        );
        $this->assertEquals($expected, $result);
    }

    public function testRegisterUseDefaultValues()
    {
        $pw = 'test';
        $data = [
            'username' => 'Reginald',
            'password' => $pw,
            'password_confirm' => $pw,
            'user_email' => 'Reginald@example.com',
            'user_type' => 'admin',
            'activate_code' => '0'
        ];
        $user = $this->Table->register($data);
        $this->assertNotEmpty($user->get('activate_code'));
        $this->assertEquals('user', $user->get('user_type'));
    }

    public function testRegisterAutoRegister()
    {
        $pw = 'test';
        $data = [
            'username' => 'Reginald',
            'password' => $pw,
            'password_confirm' => $pw,
            'user_email' => 'Reginald@example.com',
            'user_type' => 'admin',
            'activate_code' => '0'
        ];
        $user = $this->Table->register($data, true);
        $this->assertEmpty($user->get('activate_code'));
    }

    public function testRegisterValidation()
    {
        $data = [
            'username' => 'mITch',
            'user_email' => 'alice@example.com',
            'password' => 'NewUserspassword',
            'password_confirm' => 'NewUser1spassword'
        ];

        $user = $this->Table->register($data);

        $expected = [
            'password' => ['pwConfirm' => 'Passwords don\'t match.'],
            'username' => ['isUnique' => 'Name is already used.'],
            'user_email' => ['isUnique' => 'Email address is already used.'],
        ];
        $this->assertEquals($expected, $user->getErrors());
    }

    public function testRegisterValidationUsernameDisallowedChars()
    {
        $data = [
            'username' => 'Eloise in the <I>-Post',
            'user_email' => 'church@losangeles.com',
            'password' => 'Daniel',
            'password_confirm' => 'Daniel'
        ];
        $user = $this->Table->register($data);

        $this->assertArrayHasKey('username', $user->getErrors());
    }

    public function testRegisterValidationUsernameEmojiUtf()
    {
        $data = [
            'username' => '☸🏝',
            'user_email' => 'redacted@example.com',
            'password' => 'Benjamin',
            'password_confirm' => 'Benjamin'
        ];
        $user = $this->Table->register($data);

        $this->assertArrayHasKey('username', $user->getErrors());
    }

    public function testRegisterValidationUsernameIsEqualDisallowed()
    {
        $data = [
            'username' => 'Mitsch',
            'user_email' => 'mib@example.com',
            'password' => 'beforeandagain',
            'password_confirm' => 'beforeandagain'
        ];

        $result = $this->Table->register($data);
        $this->assertArrayHasKey('username', $result->getErrors());

        $data = [
            'username' => 'Mischa',
            'user_email' => 'mib@example.com',
            'password' => 'beforeandagain',
            'password_confirm' => 'beforeandagain'
        ];
        $entity = $this->Table->register($data);
        $this->assertArrayNotHasKey('username', $entity->getErrors());
    }

    /**
     * the validation should not trigger if two colliding but existing users
     * with same name get values updated, but not the username
     */
    public function testRegisterValidationUsernameIsEqualAllowed()
    {
        $data = [
            'username' => 'Liane',
            'user_email' => 'new@example.com'
        ];
        $entity = $this->Table->get(9);
        $entity = $this->Table->patchEntity($entity, $data);
        $this->assertEmpty($entity->getErrors());
        $this->assertNotFalse($this->Table->save($entity));
    }
}
