<?php

	App::uses('CacheTree', 'Lib/CacheTree');
	App::uses('SaitoUser', 'Lib/SaitoUser');

	class CacheTreeMock extends CacheTree {

		public function __construct() {
			$this->_CurrentUser = new SaitoUser();
		}

		public function setCache($data) {
			$this->_cachedEntries = $data;
		}

		public function setAllowRead($state) {
			$this->_allowRead = $state;
		}

		public function setAllowUpdate($state) {
			$this->_allowUpdate = $state;
		}

		public function __get($name) {
			if ($name === 'CurrentUser') {
				return $this->_CurrentUser;
			}
		}

	}

	/**
	 * CacheTreeComponent Test Case
	 *
	 */
	class CacheTreeTest extends CakeTestCase {

		/**
		 * setUp method
		 *
		 * @return void
		 */
		public function setUp() {
			parent::setUp();
			$this->CacheTree = new CacheTreeMock();

			$cacheData = array(
					'1' => array(
							'metadata' => array(
									'content_last_updated' => time() - 3600,
							),
							'content' => 'foo',
					),
			);
			$this->CacheTree->setCache($cacheData);
		}

		/**
		 * tearDown method
		 *
		 * @return void
		 */
		public function tearDown() {
			unset($this->CacheTree);

			parent::tearDown();
		}

		/**
		 * testIsCacheUpdatable method
		 *
		 * @return void
		 */
		public function testIsCacheUpdatableDisabled() {
			$this->CacheTree->setAllowUpdate(false);

			$this->CacheTree->CurrentUser->ReadEntries = $this->getMock('Object', [
				'isRead'
			]);
			$this->CacheTree->CurrentUser->ReadEntries
				->expects($this->never())
				->method('isRead');

			$in = array(
					'id' => 1,
					'last_answer' => date('Y-m-d H:i:s', time() - 7200),
			);
			$result = $this->CacheTree->isCacheUpdatable($in);
			$this->assertFalse($result);
		}

		public function testIsCacheUpdatable() {
			$this->CacheTree->setAllowUpdate(true);

			$this->CacheTree->CurrentUser->ReadEntries = $this->getMock('Object', [
				'isRead'
			]);
			$this->CacheTree->CurrentUser->ReadEntries
				->expects($this->once())
				->method('isRead')
				->will($this->returnValue(true));

			$in = array(
					'id' => 1,
					'last_answer' => date('Y-m-d H:i:s', time() - 7200),
			);
			$result = $this->CacheTree->isCacheUpdatable($in);
			$this->assertTrue($result);
		}

		public function testIsCacheUpdatableNewToUser() {
			$this->CacheTree->CurrentUser->ReadEntries = $this->getMock('Object', [
				'isRead'
			]);
			$this->CacheTree->CurrentUser->ReadEntries
				->expects($this->once())
				->method('isRead')
				->will($this->returnValue(false));

			$this->CacheTree->setAllowUpdate(true);

			$in = array(
					'id' => 1,
					'last_answer' => date('Y-m-d H:i:s', time() - 1800),
			);
			$result = $this->CacheTree->isCacheUpdatable($in);
			$this->assertFalse($result);
		}

		public function testIsCacheValidReadDisabled() {
			$this->CacheTree->CurrentUser->ReadEntries = $this->getMock('Object', [
				'isRead'
			]);
			$this->CacheTree->CurrentUser->ReadEntries
				->expects($this->never())
				->method('isRead');

			$this->CacheTree->setAllowRead(false);

			$in = array(
					'id' => 1,
					'last_answer' => date('Y-m-d H:i:s', time() - 7200),
			);
			$result = $this->CacheTree->isCacheValid($in);
			$this->assertFalse($result);
		}

		public function testIsCacheValid() {
			$this->CacheTree->CurrentUser->ReadEntries = $this->getMock('Object', [
				'isRead'
			]);
			$this->CacheTree->CurrentUser->ReadEntries
				->expects($this->once())
				->method('isRead')
				->will($this->returnValue(true));

			$this->CacheTree->setAllowRead(true);
			$in = array(
					'id' => 1,
					'last_answer' => date('Y-m-d H:i:s', time() - 7200),
			);
			$result = $this->CacheTree->isCacheValid($in);
			$this->assertTrue($result);
		}

		public function testIsCacheValidNewAnswerForUser() {
			$this->CacheTree->CurrentUser->ReadEntries = $this->getMock('Object', [
				'isRead'
			]);
			$this->CacheTree->CurrentUser->ReadEntries
				->expects($this->once())
				->method('isRead')
				->will($this->returnValue(false));

			$this->CacheTree->setAllowRead(true);
			$in = array(
					'id' => 1,
					'last_answer' => date('Y-m-d H:i:s', time() - 7200),
			);
			$result = $this->CacheTree->isCacheValid($in);
			$this->assertFalse($result);
		}

		/**
		 * tests that cache is invalid if thread has new answers
		 */
		public function testIsCacheValidNewAnswerInThread() {
			$this->CacheTree->setAllowRead(true);

			$in = array(
					'id' => 1,
					'last_answer' => date('Y-m-d H:i:s', time() - 1800),
			);
			$result = $this->CacheTree->isCacheValid($in);
			$this->assertFalse($result);
		}

		public function testReset() {
			// setup
			$mockData = ['foo' => 'bar'];
			$this->CacheTree->setCache($mockData);
			$this->CacheTree->setAllowRead(true);
			$result = $this->CacheTree->read();
			$this->assertEquals($result, $mockData);

			// test
			$this->CacheTree->reset();
			$result = $this->CacheTree->read();
			$this->assertEquals($result, []);
		}

	}

