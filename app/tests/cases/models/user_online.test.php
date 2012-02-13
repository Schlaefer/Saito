<?php
/* UserOnline Test cases generated on: 2011-04-28 21:04:20 : 1304018720*/
App::import('Model', 'UserOnline');
App::import('Component', 'Security');

class UserOnlineTest extends CakeTestCase {
	var $fixtures = array('app.user_online', 'app.user', 'app.entry', 'app.category', 'app.upload');

	public function testSetOnline() {

		//* argument id test
		$result = false;
		try {
			$this->UserOnline->setOnline('');
		} catch (Exception $exc) {
			$result = true;
		}
		$this->assertTrue($result);

		//* argument loggedIn test
		$result = false;
		try {
			$this->UserOnline->setOnline(5);
		} catch (Exception $exc) {
			$result = true;
		}
		$this->assertTrue($result);


		//* insert registered user
		$user_id = 5;
		$this->_startUsersOnline[0]['UserOnline'] = array ( 'id' => '1', 'user_id' => '5', 'time' => time(),  'logged_in' => 1 );
		$this->UserOnline->setOnline($user_id, TRUE);

		$this->UserOnline->contain();
		$result = $this->UserOnline->find('all');
		$expected = $this->_startUsersOnline;
		$this->assertEqual($result, $expected);

		//* insert anonymous user
		session_id('session_id_test');
		$user_id = session_id();
		$this->_startUsersOnline[1]['UserOnline'] = array ( 'id' => '2', 'user_id' => substr(($user_id), 0, 32), 'time' => time(),  'logged_in' => 0 );
		$this->UserOnline->setOnline($user_id, FALSE);

		$this->UserOnline->contain();
		$result = $this->UserOnline->find('all');
		$expected = $this->_startUsersOnline;
		$this->assertEqual($result, $expected);

		/*** Second 1 ***/
		sleep(1);

		//* update registered user before time
		$user_id = 5;
		$this->UserOnline->setOnline($user_id, TRUE);

		$this->UserOnline->contain();
		$result = $this->UserOnline->find('all');
		$expected = $this->_startUsersOnline;
		$this->assertEqual($result, $expected);

		//* update anonymous user before time
		session_id('session_id_test');
		$user_id = session_id();
		$this->UserOnline->setOnline($user_id, FALSE);

		$this->UserOnline->contain();
		$result = $this->UserOnline->find('all');
		$expected = $this->_startUsersOnline;
		$this->assertEqual($result, $expected);

		/*** Second 2 ***/
		sleep(1);

		//* update anonymous user after time
		$this->UserOnline->timeUntilOffline = 1;
		session_id('session_id_test');
		$user_id = session_id();
		$this->_startUsersOnline = array();
		$this->_startUsersOnline[0]['UserOnline'] = array ( 'id' => '2', 'user_id' => substr(($user_id), 0, 32), 'time' => time(),  'logged_in' => 0 );
		$this->UserOnline->setOnline($user_id, FALSE);

		$this->UserOnline->contain();
		$result = $this->UserOnline->find('all');
		$expected = $this->_startUsersOnline;
		$this->assertEqual($result, $expected);

		}

	function testSetOffline() {

		//* insert new user
		$user_id = 5;
		$this->_startUsersOnline[0]['UserOnline'] = array ( 'id' => '1', 'user_id' => '5', 'time' => time(),  'logged_in' => 1 );
		$this->UserOnline->setOnline($user_id, TRUE);

		//* test if user is inserted
		$this->UserOnline->contain();
		$result = $this->UserOnline->find('all');
		$expected = $this->_startUsersOnline;
		$this->assertEqual($result, $expected);

		//* try to delte new user
		$this->UserOnline->setOffline($user_id);
		$this->UserOnline->contain();
		$result = $this->UserOnline->find('all');
		$expected = array();
		$this->assertEqual($result, $expected);

		}

	function testDeleteOutdated () {
		$this->UserOnline->timeUntilOffline = 1;

		//* test remove outdated
		$user_id = 5;
		$this->UserOnline->setOnline($user_id, TRUE);
		sleep(2);
		$user_id = 6;
		$this->_startUsersOnline[]['UserOnline'] = array ( 'id' => '2', 'user_id' => '6', 'time' => time(),  'logged_in' => 1 );
		$this->UserOnline->setOnline($user_id, TRUE);

		$this->UserOnline->contain();
		$result = $this->UserOnline->find('all');
		$expected = $this->_startUsersOnline;
		$this->assertEqual($result, $expected);

	}

	public function testGetLoggedIn() {

		//* TEST empty results
		$result = $this->UserOnline->getLoggedIn();
		$expected = array();
		$this->assertEqual($result, $expected);

		//* TEST
		$user_id = 3;
		$this->UserOnline->setOnline($user_id, TRUE);

		session_id('session_id_test');
		$user_id = session_id();
		$this->UserOnline->setOnline($user_id, FALSE);

		$result 	= $this->UserOnline->getLoggedIn();
		$expected[] = array(
						'User'	=> array(
								'id'	=> 3,
								'username'	=> 'Ulysses',
								'user_type'	=> 'user',
						)
			);
		$this->assertEqual($result, $expected);
	}

	function startTest($message) {
		$this->UserOnline =& ClassRegistry::init('UserOnline');
		
		$this->_startUsersOnline = array();
	}

	function endTest() {
		unset($this->UserOnline);
		ClassRegistry::flush();
	}

}
?>