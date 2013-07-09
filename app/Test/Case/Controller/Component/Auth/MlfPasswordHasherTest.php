<?php

	App::uses('MlfPasswordHasher', 'Controller/Component/Auth');

	class MlfPasswordHasherTest extends CakeTestCase {

		public function setUp() {
			$this->Hasher = new MlfPasswordHasher();
		}

		public function tearDown() {
			unset($this->Hasher);
		}

		public function testPassword() {
			$password = 'Rosinenbrötchen';
			$hash     = 'df7d879155bec3f2674c2b3e03fe9086';
			$this->assertTrue($this->Hasher->check($password, $hash));

			// test own hash
			$password = 'Rosinenbrötchen';
			$hash     = $this->Hasher->hash($password);
			$this->assertTrue($this->Hasher->check($password, $hash));

			$this->assertFalse($this->Hasher->check(mt_rand(1, 99999), $hash));
		}

	}
