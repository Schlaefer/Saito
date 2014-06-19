<?php

	App::uses('SaitoUser', 'Lib/SaitoUser');

	class SaitoUserTest extends CakeTestCase {

		public function testGetSettings() {
			//# initialize with real user
			$user = [
				'id' => '1',
				'username' => 'Bob',
				'password' => 'foo',
			];
			$this->SaitoUser->setSettings($user);
			$result = $this->SaitoUser->getSettings();
			$this->assertEquals($user, $result);

			//# initialize with real user
			$user = null;
			$this->SaitoUser->setSettings($user);
			$result = $this->SaitoUser->getSettings();
			$this->assertFalse(empty($result) === false);
		}

		public function testGetId() {
			//# initialize with real user
			$user = [
				'id' => '2',
			];
			$this->SaitoUser->setSettings($user);
			$result = $this->SaitoUser->getId();
			$this->assertEquals(2, $result);

			//# initialize with empty
			$user = null;
			$this->SaitoUser->setSettings($user);
			$result = $this->SaitoUser->getId();
			$this->assertTrue(empty($result) === true);

			$user = false;
			$this->SaitoUser->setSettings($user);
			$result = $this->SaitoUser->getId();
			$this->assertTrue(empty($result) === true);

			$user = '';
			$this->SaitoUser->setSettings($user);
			$result = $this->SaitoUser->getId();
			$this->assertTrue(empty($result) === true);
		}

		public function testIsLoggedIn() {
			//# initialize with real user
			$user = [
				'id' => '2',
			];
			$this->SaitoUser->setSettings($user);
			$result = $this->SaitoUser->isLoggedIn();
			$this->assertTrue($result);

			//# initialize with empty
			$user = null;
			$this->SaitoUser->setSettings($user);
			$result = $this->SaitoUser->isLoggedIn();
			$this->assertFalse($result);

			$user = false;
			$this->SaitoUser->setSettings($user);
			$result = $this->SaitoUser->isLoggedIn();
			$this->assertFalse($result);

			$user = '';
			$this->SaitoUser->setSettings($user);
			$result = $this->SaitoUser->isLoggedIn();
			$this->assertFalse($result);
		}

		public function testIsLoggedInUserIdIsMissing() {
			// missing 'id' key
			$user = ['username' => 'foo'];
			$this->SaitoUser->setSettings($user);
			$this->assertFalse($this->SaitoUser->isLoggedIn());
		}

		public function testIsLoggedInUserIdIsZero() {
			$user = ['id' => 0];
			$this->SaitoUser->setSettings($user);
			$this->assertFalse($this->SaitoUser->isLoggedIn());
		}

		public function testIsLoggedInUserIdIsStringZero() {
			$user = ['id' => '0'];
			$this->SaitoUser->setSettings($user);
			$this->assertFalse($this->SaitoUser->isLoggedIn());
		}

		public function testIsMod() {
			//# anon
			$user = null;
			$this->SaitoUser->setSettings($user);
			$result = $this->SaitoUser->isMod();
			$this->assertFalse($result);

			//# user
			$user = array(
				'id' => '2',
				'user_type' => 'user',
			);
			$this->SaitoUser->setSettings($user);
			$result = $this->SaitoUser->isMod();
			$this->assertFalse($result);

			//# initialize with real user
			$user = array(
				'id' => '2',
				'user_type' => 'mod',
			);
			$this->SaitoUser->setSettings($user);
			$result = $this->SaitoUser->isMod();
			$this->assertTrue($result);

			//# admin
			$user = array(
				'id' => '2',
				'user_type' => 'admin',
			);
			$this->SaitoUser->setSettings($user);
			$result = $this->SaitoUser->isMod();
			$this->assertTrue($result);
		}

		public function testIsModOnly() {
			//# anon
			$user = null;
			$this->SaitoUser->setSettings($user);
			$result = $this->SaitoUser->isModOnly();
			$this->assertFalse($result);

			//# user
			$user = array(
				'id' => '2',
				'user_type' => 'user',
			);
			$this->SaitoUser->setSettings($user);
			$result = $this->SaitoUser->isModOnly();
			$this->assertFalse($result);

			//# initialize with real user
			$user = array(
				'id' => '2',
				'user_type' => 'mod',
			);
			$this->SaitoUser->setSettings($user);
			$result = $this->SaitoUser->isModOnly();
			$this->assertTrue($result);

			//# admin
			$user = array(
				'id' => '2',
				'user_type' => 'admin',
			);
			$this->SaitoUser->setSettings($user);
			$result = $this->SaitoUser->isModOnly();
			$this->assertFalse($result);
		}

		public function testIsAdmin() {
			//# anon
			$user = null;
			$this->SaitoUser->setSettings($user);
			$result = $this->SaitoUser->isAdmin();
			$this->assertFalse($result);

			//# user
			$user = array(
				'id' => '2',
				'user_type' => 'user',
			);
			$this->SaitoUser->setSettings($user);
			$result = $this->SaitoUser->isAdmin();
			$this->assertFalse($result);

			//# initialize with real user
			$user = array(
				'id' => '2',
				'user_type' => 'mod',
			);
			$this->SaitoUser->setSettings($user);
			$result = $this->SaitoUser->isAdmin();
			$this->assertFalse($result);

			//# admin
			$user = array(
				'id' => '2',
				'user_type' => 'admin',
			);
			$this->SaitoUser->setSettings($user);
			$result = $this->SaitoUser->isAdmin();
			$this->assertTrue($result);
		}

		public function testGetMaxAccession() {
			//# anon
			$user = null;
			$this->SaitoUser->setSettings($user);
			$result = $this->SaitoUser->getMaxAccession();
			$this->assertEquals(0, $result);

			//# user
			$user = array(
				'id' => '2',
				'user_type' => 'user',
			);
			$this->SaitoUser->setSettings($user);
			$result = $this->SaitoUser->getMaxAccession();
			$this->assertEquals(1, $result);

			//# initialize with real user
			$user = array(
				'id' => '2',
				'user_type' => 'mod',
			);
			$this->SaitoUser->setSettings($user);
			$result = $this->SaitoUser->getMaxAccession();
			$this->assertEquals(2, $result);

			//# admin
			$user = array(
				'id' => '2',
				'user_type' => 'admin',
			);
			$this->SaitoUser->setSettings($user);
			$result = $this->SaitoUser->getMaxAccession();
			$this->assertEquals(3, $result);
		}

		public function testIsUser() {
			//# anon
			$user = null;
			$this->SaitoUser->setSettings($user);
			$result = $this->SaitoUser->isUser();
			$this->assertFalse($result);

			//# user
			$user = array(
				'id' => '2',
				'user_type' => 'user',
			);
			$this->SaitoUser->setSettings($user);
			$result = $this->SaitoUser->isUser();
			$this->assertTrue($result);

			//# initialize with real user
			$user = array(
				'id' => '2',
				'user_type' => 'mod',
			);
			$this->SaitoUser->setSettings($user);
			$result = $this->SaitoUser->isUser();
			$this->assertTrue($result);

			//# admin
			$user = array(
				'id' => '2',
				'user_type' => 'admin',
			);
			$this->SaitoUser->setSettings($user);
			$result = $this->SaitoUser->isUser();
			$this->assertTrue($result);
		}

		public function testIsSame() {
			$current = ['id' => 2];
			$this->SaitoUser->setSettings($current);

			//# test
			$tests = [
				['in' => 1, 'expected' => false],
				['in' => 2, 'expected' => true],
				['in' => '1', 'expected' => false],
				['in' => '2', 'expected' => true],
				['in' => ['id' => 1], 'expected' => false],
				['in' => ['id' => 2], 'expected' => true],
				['in' => ['id' => '1'], 'expected' => false],
				['in' => ['id' => '2'], 'expected' => true],
				['in' => ['User' => ['id' => 1]], 'expected' => false],
				['in' => ['User' => ['id' => 2]], 'expected' => true],
				['in' => ['User' => ['id' => '1']], 'expected' => false],
				['in' => ['User' => ['id' => '2']], 'expected' => true],
			];
			foreach ($tests as $test) {
				$result = $this->SaitoUser->isSame($test['in']);
				$this->assertEquals($test['expected'], $result);
			}
		}

		public function testArrayAccessors() {
			$user = [
				'id' => '2',
				'user_type' => 'user',
			];
			$this->SaitoUser->setSettings($user);

			$this->SaitoUser['foo'] = 'bar';
			$this->assertEquals($this->SaitoUser['foo'], 'bar');

			$this->assertTrue(isset($this->SaitoUser['foo']));
			unset($this->SaitoUser['foo']);
			$this->assertFalse(isset($this->SaitoUser['foo']));
		}

		public function testmockUserType() {
			$user = [
				'id' => '2',
				'user_type' => 'admin',
			];
			$expected = [
				'id' => '2',
				'user_type' => 'user',
			];
			$this->SaitoUser->setSettings($user);
			$result = $this->SaitoUser->mockUserType('user');
			$this->SaitoUser->setSettings($expected);
			$this->assertEquals($this->SaitoUser, $result);
		}

		public function setUp() {
			parent::setUp();
			$this->SaitoUser = new SaitoUser();
		}

		public function tearDown() {
			parent::tearDown();
			unset($this->SaitoUser);
		}

	}
