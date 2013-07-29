<?php

	App::uses('UserOnline', 'Model');
	App::uses('Security', 'Utility');

	class UserOnlineTest extends CakeTestCase {

		public $fixtures = array( 'app.user_online', 'app.user', 'app.entry', 'app.category', 'app.upload' );
    protected $_fields = array( 'fields' => array('user_id', 'time', 'logged_in'));

		public function testSetOnline() {


			//* argument id test
			$result = false;
			try {
				$this->UserOnline->setOnline('');
			} catch ( Exception $exc ) {
				$result = true;
			}
			$this->assertTrue($result);

			//* argument loggedIn test
			$result = false;
			try {
				$this->UserOnline->setOnline(5);
			} catch ( Exception $exc ) {
				$result = true;
			}
			$this->assertTrue($result);


			//* insert registered user
			$user_id = 5;
			$this->_startUsersOnline[0]['UserOnline'] = array('user_id' => '5', 'time' => (string)time(), 'logged_in' => 1 );
			$this->UserOnline->setOnline($user_id, TRUE);

			$this->UserOnline->contain();
			$result = $this->UserOnline->find('all', $this->_fields);
			$expected = $this->_startUsersOnline;
			$this->assertEqual($result, $expected);

			//* insert anonymous user
			session_id('sessionIdTest');
			$user_id = session_id();
			$this->_startUsersOnline[1]['UserOnline'] = array('user_id' => substr(($user_id),
							0, 32), 'time' => time(), 'logged_in' => 0 );
			$this->UserOnline->setOnline($user_id, FALSE);

			$this->UserOnline->contain();
			$result = $this->UserOnline->find('all', $this->_fields);
			$expected = $this->_startUsersOnline;
			$this->assertEqual($result, $expected);

			/*			 * * Second 1 ** */
			sleep(1);

			//* update registered user before time
			$user_id = 5;
			$this->UserOnline->setOnline($user_id, TRUE);

			$this->UserOnline->contain();
			$result = $this->UserOnline->find('all', $this->_fields);
			$expected = $this->_startUsersOnline;
			$this->assertEqual($result, $expected);

			//* update anonymous user before time
			session_id('sessionIdTest');
			$user_id = session_id();
			$this->UserOnline->setOnline($user_id, FALSE);

			$this->UserOnline->contain();
			$result = $this->UserOnline->find('all', $this->_fields);
			$expected = $this->_startUsersOnline;
			$this->assertEqual($result, $expected);

			/*			 * * Second 2 ** */
			sleep(1);

			//* update anonymous user after time
			$this->UserOnline->timeUntilOffline = 1;
			session_id('sessionIdTest');
			$user_id = session_id();
			$this->_startUsersOnline = array( );
			$this->_startUsersOnline[0]['UserOnline'] = array('user_id' => substr(($user_id),
							0, 32), 'time' => time(), 'logged_in' => 0 );
			$this->UserOnline->setOnline($user_id, FALSE);

			$this->UserOnline->contain();
			$result = $this->UserOnline->find('all', $this->_fields);
			$expected = $this->_startUsersOnline;
			$this->assertEqual($result, $expected);
		}

		public function testSetOffline() {

			//* insert new user
			$user_id = 5;
			$this->_startUsersOnline[0]['UserOnline'] = [
				'user_id'   => '5',
				'logged_in' => 1
			];
			$this->UserOnline->setOnline($user_id, TRUE);

			//* test if user is inserted
			$this->UserOnline->contain();
			$result = $this->UserOnline->find('all', $this->_fields);
			$expected = $this->_startUsersOnline;

			$time = $result[0]['UserOnline']['time'];
			$this->assertGreaterThan(time() - 5, $time);
			unset($result[0]['UserOnline']['time'], $time);

			$this->assertEqual($result, $expected);

			//* try to delte new user
			$this->UserOnline->setOffline($user_id);
			$this->UserOnline->contain();
			$result = $this->UserOnline->find('all', $this->_fields);
			$expected = array( );
			$this->assertEqual($result, $expected);
		}

		public function testDeleteOutdated() {
			$this->UserOnline->timeUntilOffline = 1;

			//* test remove outdated
			$user_id = 5;
			$this->UserOnline->setOnline($user_id, TRUE);
			sleep(2);
			$user_id = 6;
			$this->_startUsersOnline[]['UserOnline'] = array('user_id' => '6', 'time' => time(), 'logged_in' => 1 );
			$this->UserOnline->setOnline($user_id, TRUE);

			$this->UserOnline->contain();
			$result = $this->UserOnline->find('all', $this->_fields);
			$expected = $this->_startUsersOnline;
			$this->assertEqual($result, $expected);
		}

		public function testGetLoggedIn() {

			/**
			 * test empty results, no user is logged in
			 */
			$result = $this->UserOnline->getLoggedIn();
			$expected = array( );
			$this->assertEqual($result, $expected);

			/**
			 * test
			 */
			// login one user
			$user_id = 3;
			$this->UserOnline->setOnline($user_id, TRUE);

			session_id('sessionIdTest');
			$user_id = session_id();
			$this->UserOnline->setOnline($user_id, FALSE);

			$result = $this->UserOnline->getLoggedIn();
			$expected[] = array(
					'User' => array(
							'id' => 3,
							'username' => 'Ulysses',
							'user_type' => 'user',
					)
			);
			$this->assertEqual($result, $expected);
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

?>