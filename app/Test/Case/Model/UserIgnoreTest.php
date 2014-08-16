<?php

	App::uses('UserIgnore', 'Model');

	/**
	 * UserIgnore Test Case
	 *
	 */
	class UserIgnoreTest extends CakeTestCase {

		/**
		 * @var UserIgnore
		 */
		public $UserIgnore;

		/**
		 * Fixtures
		 *
		 * @var array
		 */
		public $fixtures = array(
			'app.user',
			'app.user_ignore',
		);

		/**
		 * setUp method
		 *
		 * @return void
		 */
		public function setUp() {
			parent::setUp();
			$this->UserIgnore = ClassRegistry::init('UserIgnore');
		}

		public function testCountIngored() {
			$this->UserIgnore->ignore(1, 3);
			$this->UserIgnore->ignore(2, 3);

			$this->assertEquals(2, $this->UserIgnore->countIgnored(3));
		}

		public function testDeleteUser() {
			$this->UserIgnore->ignore(1, 2);
			$this->UserIgnore->ignore(2, 3);
			$this->UserIgnore->ignore(3, 1);

			$this->UserIgnore->deleteUser(2);

			$results = $this->UserIgnore->ignoredBy(3);
			$this->assertEquals($results[0]['User']['id'], 1);
		}

		public function testIgnore() {
			$this->UserIgnore->ignore(2, 3);

			$results = $this->UserIgnore->find('all', ['contain' => false]);
			$this->assertCount(1, $results);

			$result = $results[0]['UserIgnore'];
			$this->assertEquals($result['id'], '1');
			$this->assertEquals($result['user_id'], '2');
			$this->assertEquals($result['blocked_user_id'], '3');
			$this->assertWithinMargin(strtotime($result['timestamp']), time(), 3);

			$this->UserIgnore->ignore(2, 3);
			$results = $this->UserIgnore->find('all', ['contain' => false]);
			$this->assertCount(1, $results);

			$this->UserIgnore->ignore(3, 4);
			$results = $this->UserIgnore->find('all', ['contain' => false]);
			$this->assertCount(2, $results);
		}

		public function testIgnoredBy() {
			$this->assertEquals($this->UserIgnore->ignoredBy(3), []);
			$this->UserIgnore->ignore(3, 1);
			$this->UserIgnore->ignore(3, 5);
			$results = $this->UserIgnore->ignoredBy(3);
			$this->assertEquals($results[0]['User']['id'], 1);
			$this->assertEquals($results[0]['User']['username'], 'Alice');
			$this->assertEquals($results[1]['User']['id'], 5);
			$this->assertEquals($results[1]['User']['username'], 'Uma');
		}

		public function testRemoveOld() {
			$duration = $this->UserIgnore->duration;
			$data = [
				[
					'id' => 0,
					'user_id' => 1,
					'blocked_user_id' => 2,
					'timestamp' => date('Y-m-d H:i:s', time() - $duration - 1)
				],
				[
					'id' => 1,
					'user_id' => 1,
					'blocked_user_id' => 3,
					'timestamp' => date('Y-m-d H:i:s', time() - $duration)
				],
			];
			$this->UserIgnore->saveAll($data);
			$this->UserIgnore->removeOld();
			$results = $this->UserIgnore->ignoredBy(1);
			$this->assertCount(1, $results);
			$this->assertEquals($results[0]['User']['id'], '3');
		}

		/**
		 * tearDown method
		 *
		 * @return void
		 */
		public function tearDown() {
			unset($this->UserIgnore);

			parent::tearDown();
		}

	}
