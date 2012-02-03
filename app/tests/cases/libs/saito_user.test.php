<?php

App::import('Lib', 'SaitoUser');

class SaitoUserText extends CakeTestCase {

	public function testGetSettings() {

		//* initialize with real user
		$user = array(
				'id' => '1',
				'username' => 'Bob',
				'password' => 'foo',
		);
		$this->SaitoUser->set($user);
		$result = $this->SaitoUser->getSettings();
		$this->assertEqual($user, $result);

		//* initialize with real user
		$user = null;
		$this->SaitoUser->set($user);
		$result = $this->SaitoUser->getSettings();
		$this->assertFalse($result);

	}

	public function testGetId() {

		//* initialize with real user
		$user = array(
				'id' => '2',
		);
		$this->SaitoUser->set($user);
		$result = $this->SaitoUser->getId();
		$this->assertEqual(2, $result);

		//* initialize with empty
		$user = null;
		$this->SaitoUser->set($user);
		$result = $this->SaitoUser->getId();
		$this->assertFalse($result);

		$user = false;
		$this->SaitoUser->set($user);
		$result = $this->SaitoUser->getId();
		$this->assertFalse($result);

		$user = '';
		$this->SaitoUser->set($user);
		$result = $this->SaitoUser->getId();
		$this->assertFalse($result);

	}

	public function testIsLoggedIn() {
		//* initialize with real user
		$user = array(
				'id' => '2',
		);
		$this->SaitoUser->set($user);
		$result = $this->SaitoUser->isLoggedIn();
		$this->assertTrue($result);

		//* initialize with empty
		$user = null;
		$this->SaitoUser->set($user);
		$result = $this->SaitoUser->isLoggedIn();
		$this->assertFalse($result);

		$user = false;
		$this->SaitoUser->set($user);
		$result = $this->SaitoUser->isLoggedIn();
		$this->assertFalse($result);

		$user = '';
		$this->SaitoUser->set($user);
		$result = $this->SaitoUser->isLoggedIn();
		$this->assertFalse($result);

	}

	public function testIsMod() {

		//* anon
		$user = null;
		$this->SaitoUser->set($user);
		$result = $this->SaitoUser->isMod();
		$this->assertFalse($result);

		//* user
		$user = array(
				'id' => '2',
				'user_type' => 'user',
		);
		$this->SaitoUser->set($user);
		$result = $this->SaitoUser->isMod();
		$this->assertFalse($result);

		//* initialize with real user
		$user = array(
				'id' => '2',
				'user_type' => 'mod',
		);
		$this->SaitoUser->set($user);
		$result = $this->SaitoUser->isMod();
		$this->assertTrue($result);

		//* admin
		$user = array(
				'id' => '2',
				'user_type' => 'admin',
		);
		$this->SaitoUser->set($user);
		$result = $this->SaitoUser->isMod();
		$this->assertTrue($result);

	}

	public function testIsAdmin() {

		//* anon
		$user = null;
		$this->SaitoUser->set($user);
		$result = $this->SaitoUser->isAdmin();
		$this->assertFalse($result);

		//* user
		$user = array(
				'id' => '2',
				'user_type' => 'user',
		);
		$this->SaitoUser->set($user);
		$result = $this->SaitoUser->isAdmin();
		$this->assertFalse($result);

		//* initialize with real user
		$user = array(
				'id' => '2',
				'user_type' => 'mod',
		);
		$this->SaitoUser->set($user);
		$result = $this->SaitoUser->isAdmin();
		$this->assertFalse($result);

		//* admin
		$user = array(
				'id' => '2',
				'user_type' => 'admin',
		);
		$this->SaitoUser->set($user);
		$result = $this->SaitoUser->isAdmin();
		$this->assertTrue($result);

	}

	public function testGetMaxAccession() {

		//* anon
		$user = null;
		$this->SaitoUser->set($user);
		$result = $this->SaitoUser->getMaxAccession();
		$this->assertEqual(0, $result);

		//* user
		$user = array(
				'id' => '2',
				'user_type' => 'user',
		);
		$this->SaitoUser->set($user);
		$result = $this->SaitoUser->getMaxAccession();
		$this->assertEqual(1, $result);

		//* initialize with real user
		$user = array(
				'id' => '2',
				'user_type' => 'mod',
		);
		$this->SaitoUser->set($user);
		$result = $this->SaitoUser->getMaxAccession();
		$this->assertEqual(2, $result);

		//* admin
		$user = array(
				'id' => '2',
				'user_type' => 'admin',
		);
		$this->SaitoUser->set($user);
		$result = $this->SaitoUser->getMaxAccession();
		$this->assertEqual(3, $result);

	}

	public function testIsUser() {

		//* anon
		$user = null;
		$this->SaitoUser->set($user);
		$result = $this->SaitoUser->isUser();
		$this->assertFalse($result);

		//* user
		$user = array(
				'id' => '2',
				'user_type' => 'user',
		);
		$this->SaitoUser->set($user);
		$result = $this->SaitoUser->isUser();
		$this->assertTrue($result);

		//* initialize with real user
		$user = array(
				'id' => '2',
				'user_type' => 'mod',
		);
		$this->SaitoUser->set($user);
		$result = $this->SaitoUser->isUser();
		$this->assertTrue($result);

		//* admin
		$user = array(
				'id' => '2',
				'user_type' => 'admin',
		);
		$this->SaitoUser->set($user);
		$result = $this->SaitoUser->isUser();
		$this->assertTrue($result);

	}

	public function testArrayAccessors() {

		$user = array(
				'id' => '2',
				'user_type' => 'user',
		);
		$this->SaitoUser->set($user);

		$this->SaitoUser['foo'] = 'bar';
		$this->assertEqual($this->SaitoUser['foo'], 'bar');

		$this->assertTrue(isset($this->SaitoUser['foo']));
		unset($this->SaitoUser['foo']);
		$this->assertFalse(isset($this->SaitoUser['foo']));


	}

	function startTest($message) {
		$this->SaitoUser = & new SaitoUser();

	}

	function endTest() {
		unset($this->SaitoUser);
		ClassRegistry::flush();

	}

}

?>