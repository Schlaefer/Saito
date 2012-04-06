<?php

	App::uses('User', 'Model');
	App::uses('Security', 'Utility');

	class UserTest extends CakeTestCase {

		public $fixtures = array(
				'app.user',
				'app.user_online',
				'app.entry',
				'app.category',
				'app.smiley',
				'app.smiley_code',
				'app.setting',
				'app.upload'
		);

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
			$this->assertEqual($prev_result, $result);
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
			$expected = 2;
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

			$result = $this->User->field('last_login');
			$expected = date('Y-m-d H:i:s');
			$this->assertEqual($expected, $result);

			//* increment random amount
			$this->User->incrementLogins(15);
			$expected = 16;
			$result = $this->User->field('logins');
			$this->assertEqual($expected, $result);

			$result = $this->User->field('last_login');
			$expected = date('Y-m-d H:i:s');
			$this->assertEqual($expected, $result);

			//* check we have no DB leaking
			$usersAfterIncrements = $this->User->find('count');
			$this->assertEqual($usersBeforeIncrements, $usersAfterIncrements);
		}

		public function testHashPassword() {

			//* setup
			$useSaltForUserPasswords = Configure::read('Saito.useSaltForUserPasswords');
			$salt = Configure::read('Security.salt');

			//* test without salt
			Configure::write('Saito.useSaltForUserPasswords', FALSE);
			$new_password = 'test1'; // '3e7705498e8be60520841409ebc69bc1';
			$this->User->id = 3;
			$this->User->saveField('password', $new_password);
			$expected = md5($new_password);
			$result = $this->User->field('password');
			$this->assertEqual($expected, $result);

			//* test with salt
			$testSalt = 'foo';
			Configure::write('Saito.useSaltForUserPasswords', TRUE);
			Configure::write('Security.salt', $testSalt);
			$new_password = 'test1'; // '3e7705498e8be60520841409ebc69bc1';
			$this->User->id = 3;
			$this->User->saveField('password', $new_password);
			$expected = md5($testSalt . $new_password);
			$result = $this->User->field('password');
			$this->assertEqual($expected, $result);

			//* teardown
			Configure::write('Saito.useSaltForUserPasswords', $useSaltForUserPasswords);
			Configure::write('Security.Salt', $salt);
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
					'User' => array(
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
			$this->assertTrue($this->User->save($data) == TRUE);
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
			$this->assertTrue($this->User->save($data) == TRUE);
			$this->assertFalse(array_key_exists('password_old',
							$this->User->validationErrors));
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
			$result = $this->User->read(array( 'username', 'password', 'user_email', 'user_type', 'user_view', 'registered' ));
			$expected = array_merge($data['User'],
					array(
					'registered' => date('Y-m-d H:i:s', $now),
					'user_type' => 'user',
					'user_view' => 'thread',
					)
			);
			unset($expected['password_confirm']);
			$expected['password'] = md5($pw);
			$result = $result['User'];
			$result = array_intersect_key($result, $expected);
			$this->assertEqual($result, $expected);
		}

		public function setUp() {
			Security::setHash('md5');

			Configure::write('Saito.useSaltForUserPasswords', FALSE);

			$this->User = ClassRegistry::init('User');
		}

	}

?>