<?php
	App::uses('UserRead', 'Model');

	/**
	 * UserRead Test Case
	 *
	 */
	class UserReadTest extends CakeTestCase {

		/**
		 * Fixtures
		 *
		 * @var array
		 */
		public $fixtures = array(
				'app.user_read',
				'app.user',
				'app.user_online',
				'app.bookmark',
				'app.entry',
				'app.category',
				'app.esevent',
				'app.esnotification',
				'app.upload'
		);

		public function testFoo() {
		}

		/**
		 * setUp method
		 *
		 * @return void
		 */
		public function setUp() {
			parent::setUp();
			$this->UserRead = ClassRegistry::init('UserRead');
		}

		/**
		 * tearDown method
		 *
		 * @return void
		 */
		public function tearDown() {
			unset($this->UserRead);

			parent::tearDown();
		}

	}
