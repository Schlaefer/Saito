<?php

	App::uses('BcryptAuthenticate', 'BcryptAuthenticate.Controller/Component/Auth');

	class BcryptAuthenticateTest extends CakeTestCase {

		public function testPassword() {
			$password	 = 'Rosinenbrötchen';
			$hash			 = BcryptAuthenticate::hash($password);

			$this->assertStringStartsWith('$2a$' . BcryptAuthenticate::$cost, $hash);
			$this->assertEquals(60, strlen($hash));
			$this->assertTrue(BcryptAuthenticate::checkPassword($password, $hash));

			$this->assertFalse(BcryptAuthenticate::checkPassword(mt_rand(1, 99999), $hash));
		}

		public function testShortCosts() {
			BcryptAuthenticate::$cost = 4;
			$this->expectException('InvalidArgumentException');
			$hash = BcryptAuthenticate::hash('foo');
		}

		public function setUp() {
			parent::setUp();
			$this->_cost = BcryptAuthenticate::$cost;
		}

		public function tearDown() {
			parent::tearDown();
			BcryptAuthenticate::$cost = $this->_cost;
		}

	}