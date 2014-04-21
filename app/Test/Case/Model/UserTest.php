<?php

	App::uses('User', 'Model');
	App::uses('Security', 'Utility');

	class UserMockup extends User {

		public function checkPassword($password, $hash) {
			return $this->_checkPassword($password, $hash);
		}

	}

	class UserTestCase extends CakeTestCase {

		public $fixtures = array(
				'app.bookmark',
				'app.user',
				'app.user_online',
				'app.user_read',
				'app.entry',
				'app.category',
				'app.smiley',
				'app.smiley_code',
				'app.setting',
				'app.upload',
				'app.esnotification',
				'app.esevent',
		);

		public function testEmptyUserCategoryCustom() {
			$this->User->contain();
			$result = $this->User->read('user_category_custom', 1);
			$result = $result['User']['user_category_custom'];
			$this->assertTrue(is_array($result));
			$this->assertEmpty($result);
		}

		public function testFindLatest() {
			$result = $this->User->find('latest');
			$this->assertEquals($result['User']['id'], 8);
		}

		public function testSetCategoryAll() {
			$User = $this->getMockForModel(
				'User',
				array('set', 'save')
			);
			$User->expects($this->once())
					->method('set')
					->with('user_category_active', -1);
			$User->expects($this->once())
					->method('save');
			$User->setCategory('all');
		}

		/**
		 * Set to a single category – Success
		 */
		public function testSetCategorySingle() {
			$User = $this->getMockForModel(
				'User',
				array('set', 'save')
			);
			$User->expects($this->once())
					->method('set')
					->with('user_category_active', 5);
			$User->expects($this->once())
					->method('save');
			$User->setCategory('5');
		}

		/**
		 * Set to a single category – Failure because category does not exists
		 */
		public function testSetCategorySingleNotExist() {
			$this->setExpectedException('InvalidArgumentException');
			$this->User->setCategory('fwefwe');
		}

		/**
		 * Set custom category set - Success
		 */
		public function testSetCategoryCustom() {
			$User = $this->getMockForModel(
				'User',
				array('set', 'save')
			);

			$data = array(
				'1' => '0',
				'2' => '1',
				'3' => '0',
				'5' => '0',
				'9999' => '1',
				array('foo')
			);

			$expected = array(
				'1' => false,
				'2' => true,
				'3' => false,
				'5' => false,
			);

			$User->expects($this->at(0))
					->method('set')
					->with('user_category_active', 0);
			$User->expects($this->at(1))
					->method('set')
					->with('user_category_custom', $expected);
			$User->expects($this->once())
					->method('save');
			$User->setCategory($data);
		}

/**
 * Set custom category set - Failure because no valid category is found
 */
		public function testSetCategoryCustomNotExist() {
			$this->setExpectedException('InvalidArgumentException');
			$this->User->setCategory(array('foo'));
		}

		public function testSetLastRefresh() {
			//* automatic timestamp
			$this->User->id = 3;
			$expected = date('Y-m-d H:i:s');
			$this->User->setLastRefresh();
			$result = $this->User->field('last_refresh_tmp');
			$this->assertEquals($expected, $result);

			//* with explicit timestamp
			$_prevResult = $result;

			$this->User->id = 1;
			$expected = '0000-00-00 00:00:00';
			$this->User->setLastRefresh($expected);
			$result = $this->User->field('last_refresh');
			$this->assertEquals($expected, $result);

			$result = $this->User->field('last_refresh_tmp');
			$timeDiff = strtotime($result) - strtotime($_prevResult);
			$this->assertLessThanOrEqual(1, $timeDiff);
		}

		public function testNumberOfEntry() {
			//* zero entries
			$this->User->id = 4;
			$expected = 0;
			$result = $this->User->numberOfEntries();
			$this->assertEquals($expected, $result);

			//* one entry
			$this->User->id = 2;
			$expected = 1;
			$result = $this->User->numberOfEntries();
			$this->assertEquals($expected, $result);

			//* multiple entries
			$this->User->id = 3;
			$expected = 7;
			$result = $this->User->numberOfEntries();
			$this->assertEquals($expected, $result);
		}

		public function testIncrementLogins() {
			$usersBeforeIncrements = $this->User->find('count');

			// check setup
			$this->User->id = 1;
			$expected = 0;
			$result = $this->User->field('logins');
			$this->assertEquals($expected, $result);

			//* increment one
			$expected = 1;
			$this->User->incrementLogins($this->User->id);
			$result = $this->User->field('logins');
			$this->assertEquals($expected, $result);

			$_lastLogin = new DateTime($this->User->field('last_login'));
			$now = new DateTime();
			/* on a decently performing server the timestamp is maybe not equal
			 * but within one second time diff
			 */
			$diff = $now->diff($_lastLogin);
			$result = (int)$diff->format("s");
			$expected = 1;
			$this->assertLessThanOrEqual($expected, $result);
			$this->assertGreaterThanOrEqual(0, $result);

			//* increment random amount
			$this->User->incrementLogins($this->User->id, 15);
			$expected = 16;
			$result = $this->User->field('logins');
			$this->assertEquals($expected, $result);

			$_lastLogin = new DateTime($this->User->field('last_login'));
			$now = new DateTime();
			/* on a decently performing server the timestamp is maybe not equal
			 * but within one second time diff
			 */
			$diff = $now->diff($_lastLogin);
			$result = (int)$diff->format("s");
			$expected = 1;
			$this->assertLessThanOrEqual($expected, $result);
			$this->assertGreaterThanOrEqual(0, $result);

			//* check we have no DB leaking
			$usersAfterIncrements = $this->User->find('count');
			$this->assertEquals($usersBeforeIncrements, $usersAfterIncrements);
		}

		public function testCountSolves() {
			$result = $this->User->countSolved(3);
			$this->assertEquals($result, 1);
		}

		public function testDeleteUser() {
			// test that user's notifications are deleted
			$this->User->Esnotification = $this->getMock('Esnotification',
				array('deleteAllFromUser'),
				array(false, 'esnotifications', 'test'));
			$this->User->Esnotification->expects($this->once())
					->method('deleteAllFromUser')
					->with(3)
					->will($this->returnValue(true));

			//
			$result = $this->User->findById(3);
			$this->assertTrue($result > 0);

			$entriesBeforeDelete = $this->User->Entry->find('count');

			$allBookmarksBeforeDelete = $this->User->Bookmark->find('count');
			$userBookmarksBeforeDelete = count($this->User->Bookmark->findAllByUserId(3));
			// user has bookmarks before the test
			$this->assertGreaterThan(0, $userBookmarksBeforeDelete);
			// other users have bookmarks
			$this->assertGreaterThan($userBookmarksBeforeDelete,
				$allBookmarksBeforeDelete);

			$this->User->deleteAllExceptEntries(3);

			// user is deleted
			$result = $this->User->findById(3);
			$this->assertEquals($result, array());

			// make sure we delete without cascading to associated models
			$expected = $entriesBeforeDelete;
			$result = $this->User->Entry->find('count');
			$this->assertEquals($result, $expected);

			// delete associated bookmarks
			$userBookmarksAfterDelete = count($this->User->Bookmark->findAllByUserId(3));
			$this->assertEquals($userBookmarksAfterDelete, 0);
			$allBookmarksAfterDelete = $this->User->Bookmark->find('count');
			$this->assertEquals($allBookmarksBeforeDelete - $userBookmarksBeforeDelete,
				$allBookmarksAfterDelete);
		}

		public function testSetPassword() {
			$_newPassword = 'test1';
			$this->User->id = 3;
			$this->User->saveField('password', $_newPassword);
			$result = $this->User->checkPassword($_newPassword, $this->User->field('password'));
			$this->assertTrue($result);
		}

		public function testSetPlace() {
			$this->User->id = 3;
			$success = $this->User->save([
				'User' => [
					'user_place' => 'Island',
					'user_place_lat' => -90.0000,
					'user_place_lng' => -180.0000,
					'user_place_zoom' => 1
				]
			]);
			$this->assertNotEmpty($success);
			$this->User->clear();

			$this->User->id = 3;
			$success = $this->User->save([
				'User' => [
					'user_place' => 'Island',
					'user_place_lat' => 90.0000,
					'user_place_lng' => 180.0000
				]
			]);
			$this->assertNotEmpty($success);
			$this->User->clear();

			$this->User->id = 3;
			$success = $this->User->save([
				'User' => [
					'user_place' => 'Island',
					'user_place_lat' => 90.0001,
					'user_place_log' => 180.0001
				]
			]);
			$this->assertFalse($success);
			$this->User->clear();

			$this->User->id = 3;
			$success = $this->User->save(['User' => ['user_place_lat' => -90.0001]]);
			$this->assertFalse($success);
			$this->User->clear();

			$this->User->id = 3;
			$success = $this->User->save(['User' => ['user_place_lng' => -180.0001]]);
			$this->assertFalse($success);
			$this->User->clear();

			$this->User->id = 3;
			$success = $this->User->save(['User' => ['user_place_zoom' => -1]]);
			$this->assertFalse($success);
			$this->User->clear();

			$this->User->id = 3;
			$success = $this->User->save(['User' => ['user_place_zoom' => 26]]);
			$this->assertFalse($success);
			$this->User->clear();
		}

		public function testSetUsername() {
			$User = $this->getMockForModel('User', ['_dispatchEvent']);
			$User->id = 1;
			$User->expects($this->once())
					->method('_dispatchEvent')
					->with('Model.User.username.change');
			$User->saveField('username', 'foo');
		}

		public function testActivateIdNotInt() {
			$this->setExpectedException('InvalidArgumentException');
			$this->User->activate('stro', '123');
		}

		public function testActivateCodeNotString() {
			$this->setExpectedException('InvalidArgumentException');
			$this->User->activate(123, 123);
		}

		public function testActivateUserNotFound() {
			$this->setExpectedException('InvalidArgumentException');
			$this->User->activate(123, '123');
		}

		public function testActivateUserAlreadyActivated() {
			$result = $this->User->activate(1, '123');
			$this->assertEquals('already', $result['status']);
		}

		public function testActivateUserWrongCode() {
			$result = $this->User->activate(9, '123');
			$this->assertFalse($result);
		}

		public function testActivateUserSuccess() {
			$result = $this->User->activate(9, '1548');
			$this->assertEquals('activated', $result['status']);
			$user = $this->User->findById(9);
			$this->assertEquals(0, $user['User']['activate_code']);
			$this->assertEquals($user['User'], $result['User']);
		}

		public function testAfterFind() {
			//* setting prefix for empty colors
			$this->User->id = 3;
			$data = array(
				'User' => array(
					'user_color_new_postings' => '',
					'user_color_old_postings' => '',
					'user_color_actual_posting' => '',
				),
			);
			$this->User->save($data);
			$this->User->contain();
			$expected = array(
				$this->User->name => array(
					'user_color_new_postings' => '#',
					'user_color_old_postings' => '#',
					'user_color_actual_posting' => '#',
				),
			);
			$result = $this->User->read(array( 'user_color_new_postings', 'user_color_old_postings', 'user_color_actual_posting' ));
			$this->assertEquals($expected, $result);
		}

		public function testBeforeValidate() {
			//*
			$this->User->id = 3;
			$data = array(
				'User' => array(
					'user_forum_refresh_time' => '1',
				)
			);
			$this->User->save($data);
			$expected = 1;
			$result = $this->User->field('user_forum_refresh_time');
			$this->assertEquals($result, $expected);

			//*
			$this->User->id = 3;
			$data = array(
				'User' => array(
					'user_forum_refresh_time' => '',
				)
			);
			$this->User->save($data);
			$expected = 0;
			$result = $this->User->field('user_forum_refresh_time');
			$this->assertEquals($result, $expected);
		}

		public function testValidateConfirmPassword() {
			$this->User->id = 3;
			$data = array(
				'User' => array(
					'password' => 'new_pw',
					'password_confirm' => 'new_pw_wrong'
				)
			);
			$this->assertFalse($this->User->save($data));
			$this->assertTrue(array_key_exists('password',
				$this->User->validationErrors));

			$this->User->id = 3;
			$data = array(
				'User' => array(
					'password' => 'new_pw',
					'password_confirm' => 'new_pw'
				)
			);
			$this->assertTrue($this->User->save($data) == true);
			$this->assertFalse(array_key_exists('password', $this->User->validationErrors));
		}

		public function testValidateCheckOldPassword() {
			$this->User->id = 3;
			$data = array(
				'User' => array(
					'password_old' => 'something',
					'password' => 'new_pw_2',
					'password_confirm' => 'new_pw_2',
				)
			);
			$this->assertFalse($this->User->save($data));
			$this->assertTrue(array_key_exists('password_old',
				$this->User->validationErrors));

			$data = array(
				'User' => array(
					'password_old' => 'test',
					'password' => 'new_pw_2',
					'password_confirm' => 'new_pw_2',
				)
			);
			$this->assertTrue($this->User->save($data) == true);
			$this->assertFalse(array_key_exists('password_old',
				$this->User->validationErrors));
		}

		public function testAutoUpdatePassword() {
			// test exchanging
			$_newPassword = 'testtest';
			$this->User->id = 3;
			$this->User->autoUpdatePassword($this->User->id, $_newPassword);
			$result = $this->User->checkPassword($_newPassword, $this->User->field('password'));
			$this->assertTrue($result);

			// don't exchange if up to date
			$_newPassword = 'testtest';
			$this->User->id = 6;
			$_oldPassword = $this->User->field('password');

			$this->User->autoUpdatePassword($this->User->id, $_newPassword);
			$result = $this->User->checkPassword($_newPassword,
				$this->User->field('password'));
			$this->assertTrue($result);

			$_newPassword = $this->User->field('password');
			$this->assertEquals($_oldPassword, $_newPassword);
		}

		public function testRegisterGc() {
			Configure::write('Saito.Settings.topics_per_page', 20);

			$_userCountBeforeAction = $this->User->find('count');

			$user1 = array(
				'User' => array(
					'username' => 'Reginald',
					'password' => 'test',
					'password_confirm' => 'test',
					'user_email' => 'Reginald@example.com',
					'activate_code' => 5,
				),
			);
			$user2 = array(
				'User' => array(
					'username' => 'Ronald',
					'password' => 'test',
					'password_confirm' => 'test',
					'user_email' => 'Ronald@example.com',
					'activate_code' => 539,
				),
			);
			$this->User->register($user1);
			$this->User->register($user2);

			$this->User->findByUsername('Ronald');
			$this->User->set('registered', date('Y-m-d H:i:s', time() - 90000));
			$this->User->save();

			$this->User->clearHistoryCron();
			$this->User->executeCron();

			$result = $this->User->findByUsername('Reginald');
			$this->assertTrue($result == true);

			$result = $this->User->findByUsername('Ronald');
			$this->assertEmpty($result);

			$_userCountAfterAction = $this->User->find('count');
			// (reginald stays) + (fixture user-id 9 is gone) = 0
			$this->assertEquals($_userCountBeforeAction, $_userCountAfterAction - 0);
		}

		public function testRegister() {
			// new user
			$pw = 'test';
			$data = array(
					'User' => array(
							'username' => 'Reginald',
							'password' => $pw,
							'password_confirm' => $pw,
							'user_email' => 'Reginald@example.com',
					),
			);
			$now = time();
			$result = $this->User->register($data);

			$this->assertTrue($this->User->checkPassword($pw, $this->User->field('password')));

			$result = $this->User->read(['username', 'user_email', 'user_type', 'registered']);
			$expected = array_merge($data['User'],
					['registered' => date('Y-m-d H:i:s', $now), 'user_type' => 'user']);

			unset($expected['password_confirm']);
			unset($expected['password']);

			$result = $result['User'];
			$result = array_intersect_key($result, $expected);
			$this->assertEquals($result, $expected);
		}

		public function testRegisterUseDefaultValues() {
			$pw = 'test';
			$data = [
				'User' => [
					'username' => 'Reginald',
					'password' => $pw,
					'password_confirm' => $pw,
					'user_email' => 'Reginald@example.com',
					'user_type' => 'admin',
					'activate_code' => '0'
				],
			];
			$this->User->register($data);
			$this->assertNotEmpty($this->User->field('activate_code'));
			$this->assertEquals('user', $this->User->field('user_type'));
		}

		public function testRegisterAutoRegister() {
			$pw = 'test';
			$data = [
				'User' => [
					'username' => 'Reginald',
					'password' => $pw,
					'password_confirm' => $pw,
					'user_email' => 'Reginald@example.com',
					'user_type' => 'admin',
					'activate_code' => '0'
				],
			];
			$this->User->register($data, true);
			$this->assertEmpty($this->User->field('activate_code'));
			$this->assertEquals('user', $this->User->field('user_type'));
		}

		public function testRegisterValidation() {
			$data = array(
				'User' => array(
					'username' => 'mitch',
					'user_email' => 'alice@example.com',
					'password' => 'NewUserspassword',
					'password_confirm' => 'NewUser1spassword'
				),
			);

			$result = $this->User->register($data);
			$this->assertFalse($result);

			$expected = array(
				'password' => array(
					'validation_error_pwConfirm'
				),
				'username' => array(
					'isUnique'
				),
				'user_email' => array(
					'isUnique'
				)
			);
			$this->assertEquals($this->User->validationErrors, $expected);
		}

		public function testRegisterValidationUsernameDisallowedChars() {
			$data = [
				'User' => [
					'username' => 'Eloise in the <I>-Post',
					'user_email' => 'church@losangeles.com',
					'password' => 'Daniel',
					'password_confirm' => 'Daniel'
				],
			];
			$result = $this->User->register($data);
			$this->assertFalse($result);

			$expected = ['username' => ['hasAllowedChars']];
			$this->assertEquals($this->User->validationErrors, $expected);
		}

		public function setUp() {
			Security::setHash('md5');

			Configure::write('Saito.useSaltForUserPasswords', false);

			$this->User = ClassRegistry::init(array('class' => 'UserMockup', 'alias' => 'User'));
		}

		public function tearDown() {
			unset($this->User);
			parent::tearDown();
		}

	}
