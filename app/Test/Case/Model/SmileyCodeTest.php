<?php

	App::uses('SmileyCode', 'Model');
	App::uses('CacheSupport', 'Lib/Cache');

	class SmileyCodeTest extends CakeTestCase {

		public $fixtures = ['app.smiley', 'app.smiley_code'];

		public function testCacheClearAfterDelete() {
			$this->SmileyCode = $this->getMockforModel('SmileyCode', ['clearCache']);
			$this->SmileyCode->expects($this->once())
					->method('clearCache');
			$this->SmileyCode->delete(1);
		}

		public function testCacheClearAfterSave() {
			$this->SmileyCode = $this->getMockforModel('SmileyCode', ['clearCache']);
			$this->SmileyCode->expects($this->once())
					->method('clearCache');
			$this->SmileyCode->save(['id' => 1, 'code' => ';-)']);
		}

		public function setUp() {
			parent::setUp();
			$this->SmileyCode = ClassRegistry::init('Smiley');
			$this->SmileyCode->SharedObjects['CacheSupport'] = new CacheSupport();
			$this->SmileyCode->clearCache();
		}

		public function tearDown() {
			$this->SmileyCode->clearCache();
			unset($this->SmileyCode);
			parent::tearDown();
		}

	}
