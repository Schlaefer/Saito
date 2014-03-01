<?php

	App::uses('Smiley', 'Model');
	App::uses('CacheSupport', 'Lib');

	class SmileyTest extends CakeTestCase {

		public $fixtures = array('app.smiley', 'app.smiley_code');

		public function testLoad() {
			// test loading into Configure
			$this->Smiley->load();
			$result = Configure::read('Saito.Smilies.smilies_all');
			$expected = array(
				array(
					'order' => 1,
					'icon' => 'wink.png',
					'image' => 'wink.png',
					'title' => 'Wink',
					'code' => ';)',
				),
				array(
					'order' => 2,
					'icon' => 'smile_icon.png',
					'image' => 'smile_image.png',
					'title' => 'Smile',
					'code' => ':-)',
				),
				array(
					'order' => 2,
					'icon' => 'smile_icon.png',
					'image' => 'smile_image.png',
					'title' => 'Smile',
					'code' => ';-)',
					),
			);
			$this->assertEqual($result, $expected);
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
