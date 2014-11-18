<?php

	namespace App\Test\TestCase\Model\Table;

	use App\Model\Table\UsersTable;
    use Cake\I18n\Time;
    use Cake\ORM\TableRegistry;
    use Saito\App\Registry;
    use Saito\Test\Model\Table\SaitoTableTestCase;

	class UsersTableTest extends SaitoTableTestCase {

		public $tableClass = 'Users';

		public $fixtures = [
			'app.category',
			'app.entry',
			'app.esevent',
			'app.esnotification',
			'app.setting',
			'app.smiley',
			'app.smiley_code',
			'app.upload',
			'app.user',
			'app.user_block',
			'app.user_ignore',
			'app.user_online',
			'app.user_read',
			'plugin.bookmarks.bookmark'
		];

		public function testEmptyUserCategoryCustom() {
			$User = $this->Table->get(1);
			$result = $User->get('user_category_custom');
			$this->assertTrue(is_array($result));
			$this->assertEmpty($result);
		}

		public function testFindLatest() {
			$result = $this->Table->find('latest')->firstOrFail();
			$this->assertEquals($result->get('id'), 9);
		}

		public function testSetCategoryAll() {
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
		public function testSetCategorySingle() {
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
		public function testSetCategorySingleNotExist() {
			$this->setExpectedException('\InvalidArgumentException');
			$this->Table->setCategory(1, 'fwefwe');
		}

		/**
		 * Set custom category set - Success
		 */
		public function testSetCategoryCustom() {
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
		public function testSetCategoryCustomNotExist() {
			$this->setExpectedException('InvalidArgumentException');
			$this->Table->setCategory(1, ['foo']);
		}

		public function testSetLastRefresh() {
			//= automatic timestamp
			$expected = date('Y-m-d H:i:s');
			$this->Table->setLastRefresh(3);
			$result = $this->Table->get(3)
				->get('last_refresh_tmp')
				->toDateTimeString();
			$this->assertEquals($expected, $result);
			return;

			//= with explicit timestamp
			$_prevResult = $result;

			$this->User->id = 1;
			$expected = null;
			$this->User->setLastRefresh($expected);
			$result = $this->User->field('last_refresh');
			$this->assertEquals($expected, $result);

			$result = $this->User->field('last_refresh_tmp');
			$timeDiff = strtotime($result) - strtotime($_prevResult);
			$this->assertLessThanOrEqual(1, $timeDiff);
		}

		public function testIncrementLogins() {
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

		public function testColorsSetAndGetEmpty() {
			$entity = $this->Table->newEntity(
				[
					'id' => 3,
					'user_color_new_postings' => '',
					'user_color_old_postings' => '',
					'user_color_actual_posting' => '',
				]
			);
			$this->Table->save($entity);

			$expected = [
				'user_color_new_postings' => '#',
				'user_color_old_postings' => '#',
				'user_color_actual_posting' => '#',
			];
			$result = $this->Table->get(3)->toArray();
			$result = array_intersect_key($result, $expected);
			$this->assertEquals($expected, $result);
		}

		public function testCountSolves() {
			$result = $this->Table->countSolved(3);
			$this->assertEquals($result, 1);
		}

		public function testDeleteUser() {
			// test that user's notifications are deleted
            /*
            // @todo 3.0 notifications
			$this->User->Esnotification = $this->getMockForModel('Esnotification',
				array('deleteAllFromUser'),
				array(false, 'esnotifications', 'test'));
			$this->User->Esnotification->expects($this->once())
					->method('deleteAllFromUser')
					->with(3)
					->will($this->returnValue(true));

			$this->User->Ignore = $this->getMockForModel('UserIgnore',
				['deleteUser'],
				[false, 'user_ignore', 'test']);
			$this->User->Ignore->expects($this->once())
				->method('deleteUser')
				->with(3)
				->will($this->returnValue(true));
            */

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
			$this->assertGreaterThan($userBookmarksBeforeDelete, $allBookmarksBeforeDelete);

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
		}

		public function testSetPassword() {
			$newPw = 'test1';

            $Entity = $this->Table->get(3);
            $Entity->set('password', $newPw);
            $this->Table->save($Entity);

            $Entity = $this->Table->get(3);
			$result = $this->Table->checkPassword($newPw, $Entity->get('password'));
			$this->assertTrue($result);
		}

		public function testSetPlace() {
            $data = [
                'user_place' => 'Island',
                'user_place_lat' => -90.0000,
                'user_place_lng' => -180.0000,
                'user_place_zoom' => 1
            ];
            $Entity = $this->Table->get(3);
            $this->Table->patchEntity($Entity, $data);
			$success = $this->Table->save($Entity);
			$this->assertNotEmpty($success);

			$data = [
					'user_place' => 'Island',
					'user_place_lat' => 90.0000,
					'user_place_lng' => 180.0000
			];
            $this->Table->patchEntity($Entity, $data);
            $success = $this->Table->save($Entity);
			$this->assertNotEmpty($success);

            $data = [
                'user_place' => 'Island',
                'user_place_lat' => 90.0001,
                'user_place_log' => 180.0001
            ];
            $this->Table->patchEntity($Entity, $data);
            $success = $this->Table->save($Entity);
			$this->assertFalse($success);

            $this->Table->patchEntity($Entity, ['user_place_lat' => -90.0001]);
            $success = $this->Table->save($Entity);
			$this->assertFalse($success);

			$this->Table->patchEntity($Entity, ['user_place_lng' => -180.0001]);
            $success = $this->Table->save($Entity);
			$this->assertFalse($success);

			$this->Table->patchEntity($Entity, ['user_place_zoom' => -1]);
            $success = $this->Table->save($Entity);
			$this->assertFalse($success);

			$this->Table->patchEntity($Entity, ['user_place_zoom' => 26]);
            $success = $this->Table->save($Entity);
			$this->assertFalse($success);
		}

		public function testSetUsername() {
			$Users = $this->getMockForTable('Users', ['_dispatchEvent']);
            $Entity = $Users->get(3);
			$Users->expects($this->once())
					->method('_dispatchEvent')
					->with('Cmd.Cache.clear', ['cache' => 'Thread']);
            $Users->patchEntity($Entity, ['username' => 'foo']);
            $Users->save($Entity);
		}

		public function testActivateIdNotInt() {
			$this->setExpectedException('InvalidArgumentException');
			$this->Table->activate('stro', '123');
		}

		public function testActivateCodeNotString() {
			$this->setExpectedException('InvalidArgumentException');
			$this->Table->activate(123, 123);
		}

		public function testActivateUserNotFound() {
			$this->setExpectedException('InvalidArgumentException');
			$this->Table->activate(123, '123');
		}

		public function testActivateUserAlreadyActivated() {
			$result = $this->Table->activate(1, '123');
			$this->assertEquals('already', $result['status']);
		}

		public function testActivateUserWrongCode() {
			$result = $this->Table->activate(10, '123');
			$this->assertFalse($result);
		}

		public function testActivateUserSuccess() {
			$result = $this->Table->activate(10, '1548');
			$this->assertEquals('activated', $result['status']);
			$user = $this->Table->get(10);
			$this->assertEquals(0, $user->get('activate_code'));
			$this->assertEquals($user->toArray(), $result['User']->toArray());
		}

		public function testBeforeValidate() {
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

		public function testValidateConfirmPassword() {
            $Entity = $this->Table->get(3);
            $data = [
                'password' => 'new_pw',
                'password_confirm' => 'new_pw_wrong'
            ];
            $this->Table->patchEntity($Entity, $data);
			$this->assertTrue(array_key_exists('password', $Entity->errors()));

            $Entity = $this->Table->get(3);
            $data = [
                'password' => 'new_pw',
                'password_confirm' => 'new_pw'
            ];
            $this->Table->patchEntity($Entity, $data);
            $this->assertEmpty($Entity->errors());
		}

		public function testValidateCheckOldPassword() {
            $Entity = $this->Table->get(3);
            $data = [
                'password_old' => 'something',
                'password' => 'new_pw_2',
                'password_confirm' => 'new_pw_2',
            ];
            $this->Table->patchEntity($Entity, $data);
            $this->assertTrue(array_key_exists('password_old', $Entity->errors()));

            $data = [
                'password_old' => 'test',
                'password' => 'new_pw_2',
                'password_confirm' => 'new_pw_2'
            ];
            $this->Table->patchEntity($Entity, $data);
            $this->assertFalse(array_key_exists('password_old', $Entity->errors()));
		}

		public function testAutoUpdatePassword() {
			// test exchanging
            $userId = 3;
			$newPassword = 'testtest';
			$this->Table->autoUpdatePassword($userId, $newPassword);
            $Entity = $this->Table->get($userId);
			$result = $this->Table->checkPassword($newPassword, $Entity->get('password'));
			$this->assertTrue($result);

			// don't exchange if up to date
            $userId = 6;
			$newPassword = 'testtest';
            $oldPassword = $this->Table->get($userId)->get('password');
			$this->Table->autoUpdatePassword($userId, $newPassword);
            $result = $this->Table->get($userId)->get('password');
			$this->assertEquals($oldPassword, $result);
		}

		public function testRegisterGc() {
//			Configure::write('Saito.Settings.topics_per_page', 20);

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
			$this->assertEquals($_userCountBeforeAction, $_userCountAfterAction - 0);
		}

		public function testRegisterSuccess() {
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

            $this->assertEmpty($user->errors());

            $result = $this->Table->checkPassword($pw, $user->get('password'));
			$this->assertTrue($result);

            $expected = $data + [
                'registered' => new Time($now),
                'user_type' => 'user'
            ];
            unset($expected['password'], $expected['password_confirm']);
            $result = array_intersect_key(
                $user->toArray(),
                array_fill_keys(['username', 'user_email', 'user_type', 'registered'], 1)
            );
			$this->assertEquals($expected, $result);
		}

		public function testRegisterUseDefaultValues() {
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

		public function testRegisterAutoRegister() {
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

		public function testRegisterValidation() {
            $data = [
                'username' => 'mITch',
                'user_email' => 'alice@example.com',
                'password' => 'NewUserspassword',
                'password_confirm' => 'NewUser1spassword'
            ];

			$user = $this->Table->register($data);

            $expected = [
                'password' =>
                    [
                        'pwConfirm' => 'Passwords don\'t match.',
                    ],
                'username' =>
                    [
                        'isUnique' => 'Name is already used.',
                    ],
                'user_email' =>
                    [
                        'isUnique' => 'Email address is already used.',
                    ],
            ];
			$this->assertEquals($expected, $user->errors());
		}

		public function testRegisterValidationUsernameDisallowedChars() {
            $data = [
                'username' => 'Eloise in the <I>-Post',
                'user_email' => 'church@losangeles.com',
                'password' => 'Daniel',
                'password_confirm' => 'Daniel'
            ];
			$user = $this->Table->register($data);

			$this->assertArrayHasKey('username', $user->errors());
		}

		public function testRegisterValidationUsernameIsEqualDisallowed() {
			$data = [
				'username' => 'Mitsch',
				'user_email' => 'mib@island.com',
				'password' => 'beforeandagain',
				'password_confirm' => 'beforeandagain'
			];

			$result = $this->Table->register($data);
			$this->assertArrayHasKey('username', $result->errors());

			$data = [
				'username' => 'Mischa',
				'user_email' => 'mib@island.com',
				'password' => 'beforeandagain',
				'password_confirm' => 'beforeandagain'
			];
			$entity = $this->Table->register($data);
			$this->assertEmpty($entity->errors());

		}

		/**
		 * the validation should not trigger if two colliding but existing users
		 * with same name get values updated, but not the username
		 */
		public function testRegisterValidationUsernameIsEqualAllowed() {
			$data = [
					'username' => 'Liane',
					'user_email' => 'new@example.com'
			];
			$entity = $this->Table->get(9);
			$entity = $this->Table->patchEntity($entity, $data);
			$this->assertEmpty($entity->errors());
			$this->assertNotFalse($this->Table->save($entity));
		}

			/*
		public function setUp() {
			Security::setHash('md5');

			Configure::write('Saito.useSaltForUserPasswords', false);

			$this->User = ClassRegistry::init(array('class' => 'UserMockup', 'alias' => 'User'));
		}

		public function tearDown() {
			unset($this->User);
			parent::tearDown();
		}
			*/

	}
