<?php

	use Saito\Cache\ItemCache;

	class ItemCacheMock extends ItemCache {

		public function setCacheEngine($CacheEngine) {
			$this->_CacheEngine = $CacheEngine;
		}

		public function setRaw($data) {
			$this->_cache = $data;
		}

		public function write() {
			$this->_write();
		}

	}

	class ItemCacheTest extends CakeTestCase {

		public function setUp() {
			parent::setUp();
			$this->_setupItemCache();

			$this->time = time();
		}

		protected function _setFixture() {
			$this->ItemCache->set(1, 'foo', $this->time - 3600);

			$this->fixture = [
				1 => [
					'metadata' => [
						'created' => $this->time,
						'content_last_updated' => $this->time - 3600
					],
					'content' => 'foo',
				]
			];
		}

		protected function _setupItemCache($methods = null, array $options = []) {
			$this->_cleanUp();
			$this->ItemCache = $this->getMock('ItemCacheMock', $methods,
				['test', null, $options]);
			$this->CacheEngine = $this->getMock('Object', ['read', 'write']);
			$this->ItemCache->setCacheEngine($this->CacheEngine);
		}

		public function tearDown() {
			parent::tearDown();
			$this->_cleanUp();
		}

		protected function _cleanUp() {
			unset($this->ItemCache);
			unset($this->CacheEngine);
		}

		public function testGcMaxItems() {
			$this->_setupItemCache(null, ['maxItems' => 2, 'maxItemsFuzzy' => 0]);

			$this->ItemCache->reset();
			$this->ItemCache->set('2', 'foo', 2);
			$this->ItemCache->set('4', 'bar', 4);
			$this->ItemCache->set('1', 'baz', 1);
			$this->ItemCache->set('3', 'baz', 3);

			$this->ItemCache->write();

			$cache = $this->ItemCache->get();
			$this->assertCount(2, $cache);
			$this->assertArrayHasKey('3', $cache);
			$this->assertArrayHasKey('4', $cache);
		}

		public function testGcOutdated() {
			$duration = 3600;
			$this->_setupItemCache(null, ['duration' => $duration]);

			$data = [
				0 => [
					'metadata' => ['created' => $this->time - $duration - 2,
						'content_last_updated' => $this->time],
					'content' => 'foo',
				],
				1 => [
					'metadata' => ['created' => $this->time - $duration - 1,
						'content_last_updated' => $this->time],
					'content' => 'foo',
				],
				2 => [
					'metadata' => ['created' => $this->time - $duration,
						'content_last_updated' => $this->time],
					'content' => 'foo',
				],
				3 => [
					'metadata' => ['created' => $this->time - $duration + 1,
						'content_last_updated' => $this->time],
					'content' => 'foo',
				]
			];

			$this->CacheEngine->expects($this->once())->method('read')
				->will($this->returnValue($data));

			$cache = $this->ItemCache->get();
			$this->assertArrayNotHasKey(0, $cache);
			$this->assertArrayNotHasKey(1, $cache);
			$this->assertArrayHasKey(2, $cache);
			$this->assertArrayHasKey(3, $cache);
		}

		public function testGetRaw() {
			$this->_setFixture();
			$this->assertEquals($this->fixture, $this->ItemCache->get());
		}

		public function testReset() {
			$this->ItemCache->reset();
			$this->assertEquals([], $this->ItemCache->get());
		}

	}
