<?php

	App::uses('Smiley', 'Model');
	App::uses('CacheSupport', 'Lib/Cache');

	class SmileyTest extends CakeTestCase {

		public $fixtures = array('app.smiley', 'app.smiley_code');

		public function testLoad() {
			// test loading into Configure
			$result = $this->Smiley->load();
			$expected = [
				[
					'order' => 1,
					'icon' => 'wink.svg',
					'image' => 'wink.svg',
					'title' => 'Wink',
					'code' => ';-)',
					'type' => 'image'
				],
				[
					'order' => 1,
					'icon' => 'wink.svg',
					'image' => 'wink.svg',
					'title' => 'Wink',
					'code' => ';)',
					'type' => 'image'
				],
				[
					'order' => 2,
					'icon' => 'smile_icon.png',
					'image' => 'smile_image.png',
					'title' => 'Smile',
					'code' => ':-)',
					'type' => 'image'
				],
				[
					'order' => 3,
					'icon' => 'coffee',
					'image' => 'coffee',
					'title' => 'Coffee',
					'code' => '[_]P',
					'type' => 'font'
				]
			];
			$this->assertEquals($expected, $result);
		}

		public function testCacheClearAfterDelete() {
			$this->Smiley = $this->getMockforModel('Smiley', ['clearCache']);
			$this->Smiley->expects($this->once())
					->method('clearCache');
			$this->Smiley->delete(1);
		}

		public function testCacheClearAfterSave() {
			$this->Smiley = $this->getMockforModel('Smiley', ['clearCache']);
			$this->Smiley->expects($this->once())
				->method('clearCache');
			$this->Smiley->save(['id' => 1, 'code' => ';-)']);
		}

		public function setUp() {
			parent::setUp();
			$this->Smiley = ClassRegistry::init('Smiley');
			$this->Smiley->SharedObjects['CacheSupport'] = new CacheSupport();
			$this->Smiley->clearCache();
		}

		public function tearDown() {
			$this->Smiley->clearCache();
			unset($this->Smiley);
			parent::tearDown();
		}

	}
