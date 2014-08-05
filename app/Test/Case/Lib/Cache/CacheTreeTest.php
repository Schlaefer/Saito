<?php

	App::uses('CacheTree', 'Lib/Cache');
	App::uses('SaitoUser', 'Lib/SaitoUser');

	class CacheTreeMock extends CacheTree {

		public function __construct() {
			$this->_CurrentUser = new SaitoUser();
			$this->_Cache = new ItemCache('EntrySub', null, ['maxItems' => 240]);
		}

		public function setCache($data) {
			$this->_cachedEntries = $data;
		}

		public function setItemCache($IC) {
			$this->_Cache = $IC;
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

		protected $_fixture;

		protected $_time;

		/**
		 * setUp method
		 *
		 * @return void
		 */
		public function setUp() {
			parent::setUp();
			$this->CacheTree = new CacheTreeMock();

			$this->_time = time();
			$fixtureTime = $this->_time - 3600;
			$fixtureContent = 'foo';
			$fixtureKey = 1;

			$this->CacheTree->setAllowUpdate(true);
			$this->CacheTree->set($fixtureKey, $fixtureContent, $fixtureTime);
			$this->CacheTree->setAllowUpdate(false);
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

			$this->CacheTree->CurrentUser->LastRefresh = $this->getMock('Object', [
				'isNewerThan'
			]);
			$this->CacheTree->CurrentUser->LastRefresh
				->expects($this->never())
				->method('isNewerThan');

			$in = array(
					'id' => 1,
					'last_answer' => date('Y-m-d H:i:s', time() - 7200),
			);
			$result = $this->CacheTree->isCacheUpdatable($in);
			$this->assertFalse($result);
		}

		public function testIsCacheUpdatable() {
			$this->CacheTree->setAllowUpdate(true);

			$this->CacheTree->CurrentUser->LastRefresh = $this->getMock('Object', [
				'isNewerThan'
			]);
			$this->CacheTree->CurrentUser->LastRefresh
				->expects($this->once())
				->method('isNewerThan')
				->will($this->returnValue(true));

			$in = array(
					'id' => 1,
					'last_answer' => date('Y-m-d H:i:s', time() - 7200),
			);
			$result = $this->CacheTree->isCacheUpdatable($in);
			$this->assertTrue($result);
		}

		public function testIsCacheUpdatableNewToUser() {
			$this->CacheTree->CurrentUser->LastRefresh = $this->getMock('Object', [
				'isNewerThan'
			]);
			$this->CacheTree->CurrentUser->LastRefresh
				->expects($this->once())
				->method('isNewerThan')
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
			$this->CacheTree->CurrentUser->LastRefresh = $this->getMock('Object', [
				'isNewerThan'
			]);
			$this->CacheTree->CurrentUser->LastRefresh
				->expects($this->never())
				->method('isNewerThan');

			$this->CacheTree->setAllowRead(false);

			$in = array(
					'id' => 1,
					'last_answer' => date('Y-m-d H:i:s', time() - 7200),
			);
			$result = $this->CacheTree->isCacheValid($in);
			$this->assertFalse($result);
		}

		public function testIsCacheValid() {
			$this->CacheTree->CurrentUser->LastRefresh = $this->getMock('Object', [
				'isNewerThan'
			]);
			$this->CacheTree->CurrentUser->LastRefresh
				->expects($this->once())
				->method('isNewerThan')
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
			$this->CacheTree->CurrentUser->LastRefresh = $this->getMock('Object', [
				'isNewerThan'
			]);
			$this->CacheTree->CurrentUser->LastRefresh
				->expects($this->once())
				->method('isNewerThan')
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
		 * last refresh for user is undetermined
		 */
		public function testIsCacheValidNewUser() {
			$this->CacheTree->CurrentUser->LastRefresh = $this->getMock('Object', [
				'isNewerThan'
			]);
			$this->CacheTree->CurrentUser->LastRefresh
				->expects($this->once())
				->method('isNewerThan')
				->will($this->returnValue(null));

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

	}
