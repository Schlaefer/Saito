<?php

	App::uses('CacheTreeComponent', 'Controller/Component');
	App::uses('CurrentUserComponent', 'Controller/Component');

	class CacheTreeComponentMock extends CacheTreeComponent {

		public function setCache($data) {
			$this->_cachedEntries = $data;
		}

		public function setUser($userData) {
			unset($this->_CurrentUser);
			$Collection = new ComponentCollection();
			$this->_CurrentUser = new CurrentUserComponent($Collection);
			$this->_CurrentUser->set($userData);
		}

		public function setAllowRead($state) {
			$this->_allowRead = $state;
		}

		public function setAllowUpdate($state) {
			$this->_allowUpdate = $state;
		}

	}

	/**
	 * CacheTreeComponent Test Case
	 *
	 */
	class CacheTreeComponentTest extends CakeTestCase {

		/**
		 * setUp method
		 *
		 * @return void
		 */
		public function setUp() {
			parent::setUp();
			$Collection = new ComponentCollection();
			$this->CacheTree = new CacheTreeComponentMock($Collection);

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

			$userData = array(
					'id' => 1,
					'last_refresh' => date('Y-m-d H:i:s', time() - 1800)
			);
			$this->CacheTree->setUser($userData);

			$in = array(
					'id' => 1,
					'last_answer' => date('Y-m-d H:i:s', time() - 7200),
			);
			$result = $this->CacheTree->isCacheUpdatable($in);
			$this->assertFalse($result);
		}

		public function testIsCacheUpdatable() {
			$this->CacheTree->setAllowUpdate(true);

			$userData = array(
					'id' => 1,
					'last_refresh' => date('Y-m-d H:i:s', time() - 1800)
			);
			$this->CacheTree->setUser($userData);

			$in = array(
					'id' => 1,
					'last_answer' => date('Y-m-d H:i:s', time() - 7200),
			);
			$result = $this->CacheTree->isCacheUpdatable($in);
			$this->assertTrue($result);
		}

		public function testIsCacheUpdatableNewToUser() {
			$this->CacheTree->setAllowUpdate(true);

			$userData = array(
					'id' => 1,
					'last_refresh' => date('Y-m-d H:i:s', time() - 7200)
			);
			$this->CacheTree->setUser($userData);

			$in = array(
					'id' => 1,
					'last_answer' => date('Y-m-d H:i:s', time() - 1800),
			);
			$result = $this->CacheTree->isCacheUpdatable($in);
			$this->assertFalse($result);
		}

		public function testIsCacheValidReadDisabled() {

			$userData = array(
					'id' => 1,
					'last_refresh' => date('Y-m-d H:i:s', time() - 7200)
			);
			$this->CacheTree->setUser($userData);
			$this->CacheTree->setAllowRead(false);

			$in = array(
					'id' => 1,
					'last_answer' => date('Y-m-d H:i:s', time() - 7200),
			);
			$result = $this->CacheTree->isCacheValid($in);
			$this->assertFalse($result);
		}

		public function testIsCacheValid() {

			$userData = array(
					'id' => 1,
					'last_refresh' => date('Y-m-d H:i:s', time() - 5400)
			);
			$this->CacheTree->setUser($userData);

			$this->CacheTree->setAllowRead(true);
			$in = array(
					'id' => 1,
					'last_answer' => date('Y-m-d H:i:s', time() - 7200),
			);
			$result = $this->CacheTree->isCacheValid($in);
			$this->assertTrue($result);
		}

		public function testIsCacheValidNotLoggedIn() {

			$userData = array( );
			$this->CacheTree->setUser($userData);

			$this->CacheTree->setAllowRead(true);
			$in = array(
					'id' => 1,
					'last_answer' => date('Y-m-d H:i:s', time() - 7200),
			);
			$result = $this->CacheTree->isCacheValid($in);
			$this->assertTrue($result);
		}

		public function testIsCacheValidNewAnswerForUser() {

			$userData = array(
					'id' => 1,
					'last_refresh' => date('Y-m-d H:i:s', time() - 10000)
			);
			$this->CacheTree->setUser($userData);

			$this->CacheTree->setAllowRead(true);
			$in = array(
					'id' => 1,
					'last_answer' => date('Y-m-d H:i:s', time() - 7200),
			);
			$result = $this->CacheTree->isCacheValid($in);
			$this->assertFalse($result);
		}

		public function testIsCacheValidNewAnswerInThread() {

			$userData = array(
					'id' => 1,
					'last_refresh' => date('Y-m-d H:i:s', time() - 0)
			);
			$this->CacheTree->setUser($userData);

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
			$this->assertEqual($result, $mockData);

			// test
			$this->CacheTree->reset();
			$result = $this->CacheTree->read();
			$this->assertEqual($result, []);
		}

		/**
		 * testDelete method
		 *
		 * @return void
		 */
		public function testDelete() {

		}

		/**
		 * testRead method
		 *
		 * @return void
		 */
		public function testRead() {

		}

		/**
		 * testUpdate method
		 *
		 * @return void
		 */
		public function testUpdate() {

		}

		/**
		 * testReadCache method
		 *
		 * @return void
		 */
		public function testReadCache() {

		}

		/**
		 * testSaveCache method
		 *
		 * @return void
		 */
		public function testSaveCache() {

		}

	}

