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
		public $fixtures = array(
			'app.user_block'
		);

		public function testSomething() {
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
