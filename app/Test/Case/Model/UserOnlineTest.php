<?php

	App::uses('UserOnline', 'Model');
	App::uses('Security', 'Utility');

	class UserOnlineTest extends CakeTestCase {

		public $fixtures = array(
			'app.user_online',
			'app.user',
			'app.entry',
			'app.category',
			'app.upload'
		);

		protected $_fields = array(
			'fields' => array(
				'uuid',
				'user_id',
				'time',
				'logged_in'
			)
		);

		public function testSetOnline() {
			//* insert registered user
			$_userId = 5;
			$this->_startUsersOnline[0]['UserOnline'] = [
					'uuid' => '5',
					'user_id' => 5,
					'time' => (string)time(),
					'logged_in' => true
			];
			$this->UserOnline->setOnline($_userId, true);

			$this->UserOnline->contain();
			$result = $this->UserOnline->find('all', $this->_fields);

			$this->_assertTimeIsNow($result[0]);

			$expected = $this->_startUsersOnline;
			unset($expected[0]['UserOnline']['time']);
			$this->assertEquals($result, $expected);

			//* insert anonymous user
			session_id('sessionIdTest');
			$_userId = session_id();
			$this->_startUsersOnline[1]['UserOnline'] = [
					'uuid' => substr(($_userId), 0, 32),
					'user_id' => null,
					'time' => (string)time(),
					'logged_in' => 0
			];
			$this->UserOnline->setOnline($_userId, false);

			$this->UserOnline->contain();
			$result = $this->UserOnline->find('all', $this->_fields);
			$this->_assertTimeIsNow($result[1]);
			$result = Hash::remove($result, '{n}.UserOnline.time');
			$expected = Hash::remove($this->_startUsersOnline, '{n}.UserOnline.time');
			$this->assertEquals($result, $expected);

			/*			 * * Second 1 ** */
			sleep(1);

			//* update registered user before time
			$_userId = 5;
			$this->UserOnline->setOnline($_userId, true);

			$this->UserOnline->contain();
			$result = $this->UserOnline->find('all', $this->_fields);
			$result = Hash::remove($result, '{n}.UserOnline.time');
			$expected = Hash::remove($this->_startUsersOnline, '{n}.UserOnline.time');
			$this->assertEquals($result, $expected);

			//* update anonymous user before time
			session_id('sessionIdTest');
			$_userId = session_id();
			$this->UserOnline->setOnline($_userId, false);

			$this->UserOnline->contain();
			$result = $this->UserOnline->find('all', $this->_fields);
			$result = Hash::remove($result, '{n}.UserOnline.time');
			$expected = Hash::remove($this->_startUsersOnline, '{n}.UserOnline.time');
			$this->assertEquals($result, $expected);

			/*			 * * Second 2 ** */
			sleep(1);

			//* update anonymous user after time
			$this->UserOnline->timeUntilOffline = 1;
			session_id('sessionIdTest');
			$_userId = session_id();
			$this->_startUsersOnline = [];
			$this->_startUsersOnline[0]['UserOnline'] = [
				'uuid' => substr(($_userId), 0, 32),
				'user_id' => null,
				'logged_in' => false
			];
			$this->UserOnline->setOnline($_userId, false);

			$this->UserOnline->contain();
			$result = $this->UserOnline->find('all', $this->_fields);

			$this->_assertTimeIsNow($result[0]);

			$expected = $this->_startUsersOnline;
			$this->assertEquals($result, $expected);
		}

		public function testSetOffline() {
			//* insert new user
			$_userId = 5;
			$this->_startUsersOnline[0]['UserOnline'] = [
				'uuid' => '5',
				'user_id' => 5,
				'logged_in' => 1
			];
			$this->UserOnline->setOnline($_userId, true);

			//* test if user is inserted
			$this->UserOnline->contain();
			$result = $this->UserOnline->find('all', $this->_fields);
			$expected = $this->_startUsersOnline;

			$time = $result[0]['UserOnline']['time'];
			$this->assertGreaterThan(time() - 5, $time);
			unset($result[0]['UserOnline']['time'], $time);

			$this->assertEquals($result, $expected);

			//* try to delete new user
			$this->UserOnline->setOffline($_userId);
			$this->UserOnline->contain();
			$result = $this->UserOnline->find('all', $this->_fields);
			$expected = [];
			$this->assertEquals($result, $expected);
		}

		public function testDeleteOutdated() {
			$this->UserOnline->timeUntilOffline = 1;

			//* test remove outdated
			$_userId = 5;
			$this->UserOnline->setOnline($_userId, true);
			sleep(2);
			$_userId = 6;
			$this->_startUsersOnline[]['UserOnline'] = [
				'uuid' => '6',
				'user_id' => 6,
				'time' => time(),
				'logged_in' => true
			];
			$this->UserOnline->setOnline($_userId, true);

			$this->UserOnline->contain();
			$result = $this->UserOnline->find('all', $this->_fields);

			$this->_assertTimeIsNow($result[0]);

			$expected = $this->_startUsersOnline;
			unset(
				$expected[0]['UserOnline']['time'],
				$result[0]['UserOnline']['time']
			);
			$this->assertEquals($result, $expected);
		}

		public function testGetLoggedIn() {
			/*
			 * test empty results, no user is logged in
			 */
			$result = $this->UserOnline->getLoggedIn();
			$expected = array( );
			$this->assertEquals($result, $expected);

			/*
			 * test
			 */
			// login one user
			$_userId = 3;
			$this->UserOnline->setOnline($_userId, true);

			session_id('sessionIdTest');
			$_userId = session_id();
			$this->UserOnline->setOnline($_userId, false);

			$result = $this->UserOnline->getLoggedIn();
			$expected[] = array(
					'User' => array(
							'id' => 3,
							'username' => 'Ulysses',
							'user_type' => 'user',
					)
			);
			$this->assertEquals($result, $expected);
		}

		protected function _assertTimeIsNow(&$UserOnline) {
			$this->assertWithinMargin($UserOnline['UserOnline']['time'], time(), 1);
			unset($UserOnline['UserOnline']['time']);
		}

		public function setUp() {
			parent::setUp();
			$this->UserOnline = ClassRegistry::init('UserOnline');

			$this->_startUsersOnline = array();
		}

		public function tearDown() {
			unset($this->UserOnline);
			parent::tearDown();
		}

	}
