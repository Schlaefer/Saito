<?php

	App::uses('SaitoCacheTree', 'Lib');

	class SaitoCacheTreeMock extends SaitoCacheTree {

		public function setCache($data) {
			self::$_cachedEntries = $data;
		}

	}

	class SaitoCacheTreeTest extends CakeTestCase {

		public function testIsCacheCurrent() {


			// Setup
			$data = array(
					'1' => array(
							'time' => time() - 3600,
							'content' => 'foo',
					),
			);

			$this->SaitoCacheTree->setCache($data);

			SaitoCacheTree::disable();
			$entry = array(
					'id' => 1,
					'last_answer' => date("Y-m-d H:i:s", time() - 7200),
			);
			$result = $this->SaitoCacheTree->isCacheCurrent($entry);
			$this->assertFalse($result);

			SaitoCacheTree::enable();
			$entry = array(
					'id' => 1,
					'last_answer' => date("Y-m-d H:i:s", time() - 7200),
			);
			$result = $this->SaitoCacheTree->isCacheCurrent($entry);
			$this->assertTrue($result);

			$entry = array(
					'id' => 1,
					'last_answer' => date("Y-m-d H:i:s", time() - 1800),
			);
			$result = $this->SaitoCacheTree->isCacheCurrent($entry);
			$this->assertFalse($result);

			$entry = array(
					'id' => 1,
					'last_answer' => NULL,
			);
			$result = $this->SaitoCacheTree->isCacheCurrent($entry);
			$this->assertFalse($result);

			$entry = array(
					'id' => 1,
			);
			$result = $this->SaitoCacheTree->isCacheCurrent($entry);
			$this->assertFalse($result);
		}

		public function tearDown() {
			parent::tearDown();
			SaitoCacheTree::disable();
		}

		public function setUp() {
			$this->SaitoCacheTree = new SaitoCacheTreeMock();
		}

	}

?>