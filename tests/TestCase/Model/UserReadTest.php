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

		/**
		 * tests that only new entries are stored to the DB
		 */
		public function testSetEntriesForUserExistingEntry() {
			$userId = 1;
			$entryId = 2;

			$User = $this->getMockForModel('UserRead', ['create', 'getUser']);

			$User->expects($this->once())
				->method('getUser')
				->with($userId)
				->will($this->returnValue([$entryId]));
			$User->expects($this->never())->method('create');

			$User->setEntriesForUser([$entryId], $userId);
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
