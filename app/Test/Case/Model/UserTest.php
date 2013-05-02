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
			$result	 = $this->User->read('user_category_custom', 1);
			$result	 = $result['User']['user_category_custom'];
			$this->assertTrue(is_array($result));
			$this->assertEmpty($result);
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
			$this->expectException('InvalidArgumentException');
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
			$this->expectException('InvalidArgumentException');
			$this->User->setCategory(array('foo'));
		}

		public function testSetLastRefresh() {
			//* automatic timestamp
			$this->User->id = 3;
			$expected = date('Y-m-d H:i:s');
			$this->User->setLastRefresh();
			$result = $this->User->field('last_refresh_tmp');
			$this->assertEqual($expected, $result);

			//* with explicit timestamp
			$prev_result = $result;

			$this->User->id = 1;
			$expected = '0000-00-00 00:00:00';
			$this->User->setLastRefresh($expected);
			$result = $this->User->field('last_refresh');
			$this->assertEqual($expected, $result);

			$result = $this->User->field('last_refresh_tmp');
			$timeDiff = strtotime($result) - strtotime($prev_result);
			$this->assertLessThanOrEqual(1, $timeDiff);
		}

		public function testNumberOfEntry() {

			//* zero entries
			$this->User->id = 4;
			$expected = 0;
			$result = $this->User->numberOfEntries();
			$this->assertEqual($expected, $result);

			//* one entry
			$this->User->id = 2;
			$expected = 1;
			$result = $this->User->numberOfEntries();
			$this->assertEqual($expected, $result);

			//* two entries
			$this->User->id = 3;
			$expected = 6;
			$result = $this->User->numberOfEntries();
			$this->assertEqual($expected, $result);
		}

		public function testIncrementLogins() {

			$usersBeforeIncrements = $this->User->find('count');

			//* check setup
			$this->User->id = 1;
			$expected = 0;
			$result = $this->User->field('logins');
			$this->assertEqual($expected, $result);

			//* increment one
			$expected = 1;
			$this->User->incrementLogins();
			$result = $this->User->field('logins');
			$this->assertEqual($expected, $result);

			$last_login = new DateTime($this->User->field('last_login'));
			$now = new DateTime();
			/* on a decently performing server the timestamp is maybe not equal
			 * but within one second time diff
			 */
			$diff = $now->diff($last_login);
			$result = (int)$diff->format("s");
			$expected = 1;
			$this->assertLessThanOrEqual($expected, $result);
			$this->assertGreaterThanOrEqual(0, $result);

			//* increment random amount
			$this->User->incrementLogins(15);
			$expected = 16;
			$result = $this->User->field('logins');
			$this->assertEqual($expected, $result);

			$last_login = new DateTime($this->User->field('last_login'));
			$now = new DateTime();
			/* on a decently performing server the timestamp is maybe not equal
			 * but within one second time diff
			 */
			$diff = $now->diff($last_login);
			$result = (int)$diff->format("s");
			$expected = 1;
			$this->assertLessThanOrEqual($expected, $result);
			$this->assertGreaterThanOrEqual(0, $result);

			//* check we have no DB leaking
			$usersAfterIncrements = $this->User->find('count');
			$this->assertEqual($usersBeforeIncrements, $usersAfterIncrements);
		}

    public function testDeleteUser() {

			// test that user's notifications are deleted
			$this->User->Esnotification = $this->getMock('Esnotification',
					array('deleteAllFromUser'), array(false, 'esnotifications', 'test'));
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
      $this->assertGreaterThan($userBookmarksBeforeDelete, $allBookmarksBeforeDelete);

      $this->User->deleteAllExceptEntries(3);

      // user is deleted
      $result = $this->User->findById(3);
      $this->assertEqual($result, array());

      // make sure we delete without cascading to associated models
      $expected = $entriesBeforeDelete;
      $result = $this->User->Entry->find('count');
      $this->assertEqual($result, $expected);

			// delete associated bookmarks
			$userBookmarksAfterDelete = count($this->User->Bookmark->findAllByUserId(3));
      $this->assertEqual($userBookmarksAfterDelete, 0);
			$allBookmarksAfterDelete = $this->User->Bookmark->find('count');
      $this->assertEqual($allBookmarksBeforeDelete - $userBookmarksBeforeDelete, $allBookmarksAfterDelete);

    }

		public function testSetPassword() {

			$new_password = 'test1';
			$this->User->id = 3;
			$this->User->saveField('password', $new_password);
			$result = $this->User->checkPassword($new_password, $this->User->field('password'));
			$this->assertTrue($result);

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
			$this->assertEqual($expected, $result);

			//* setting default font-size
			$this->User->id = 3;
			$expected = 1;
			$result = $this->User->field('user_font_size');
			$this->assertEqual($result, $expected);

			$this->User->id = 3;
			$data = array(
					'User' => array(
							'user_font_size' => '0.95',
					)
			);
			$this->User->save($data);
			$expected = 0.95;
			$result = $this->User->field('user_font_size');
			$this->assertEqual($result, $expected);
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
			$this->assertEqual($result, $expected);

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
			$this->assertEqual($result, $expected);
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
			$this->assertTrue(array_key_exists('password', $this->User->validationErrors));

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
			$new_password = 'testtest';
			$this->User->id = 3;
			$this->User->autoUpdatePassword($new_password);
			$result = $this->User->checkPassword($new_password, $this->User->field('password'));
			$this->assertTrue($result);

      // don't exchange if up to date
			$new_password = 'testtest';
			$this->User->id = 6;
      $old_password = $this->User->field('password');

			$this->User->autoUpdatePassword($new_password);
			$result = $this->User->checkPassword($new_password, $this->User->field('password'));
			$this->assertTrue($result);

      $new_password = $this->User->field('password');
      $this->assertEqual($old_password, $new_password);
    }

		public function testRegisterGc() {

			$user_count_before_action = $this->User->find('count');

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

			Cache::delete('Saito.Cache.registerGc');

			$result = $this->User->findByUsername('Reginald');
			$this->assertTrue($result == true);

			$result = $this->User->findByUsername('Ronald');
			$this->assertEmpty($result);

			$user_count_after_action = $this->User->find('count');
			$this->assertEqual($user_count_before_action, $user_count_after_action - 1);

		}

		public function testRegisterGcIsOnlyCalledOncePerRequest() {
			Cache::delete('Saito.Cache.registerGc');
			$User = $this->getMockForModel('User', ['deleteAll']);
			$User->expects($this->once())
					->method('deleteAll');
			$User->find('first');
			$User->find('first');
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

			$result = $this->User->read(array( 'username', 'user_email', 'user_type', 'user_view', 'registered'));
			$expected = array_merge($data['User'],
					array(
					'registered' => date('Y-m-d H:i:s', $now),
					'user_type' => 'user',
					'user_view' => 'thread',
					)
			);

			unset($expected['password_confirm']);
			unset($expected['password']);

			$result = $result['User'];
			$result = array_intersect_key($result, $expected);
			$this->assertEqual($result, $expected);

		}

		public function testRegisterValidation() {
			$data = array(
					'User' => array(
							'username'				 => 'mitch',
							'user_email'			 => 'alice@example.com',
							'password'		 => 'NewUserspassword',
							'password_confirm' => 'NewUser1spassword',
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

			$this->assertEqual($this->User->validationErrors, $expected);
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

?>