<?php
	App::uses('UserBlock', 'Model');

	/**
	 * UserBlock Test Case
	 *
	 */
	class UserBlockTest extends CakeTestCase {

		/**
		 * Fixtures
		 *
		 * @var array
		 */
		public $fixtures = [
			'app.user',
			'app.user_block'
		];

		public function testFindToGc() {
			$result = $this->UserBlock->find('toGc');
			$this->assertCount(1, $result);
		}

		public function testGc() {
			$before = $this->UserBlock->find('toGc');
			$this->assertCount(1, $before);

			$this->UserBlock->gc();

			$after = $this->UserBlock->find('toGc');
			$this->assertCount(0, $after);
		}

		/**
		 * setUp method
		 *
		 * @return void
		 */
		public function setUp() {
			parent::setUp();
			$this->UserBlock = ClassRegistry::init('UserBlock');
		}

		/**
		 * tearDown method
		 *
		 * @return void
		 */
		public function tearDown() {
			unset($this->UserBlock);

			parent::tearDown();
		}

	}
