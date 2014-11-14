<?php

	App::uses('Category', 'Model');

	class CategoryTest extends CakeTestCase {

		public $fixtures = array( 'app.category', 'app.user', 'app.upload', 'app.user_online', 'app.entry' );

		public function testGetCategoriesSelectForAccession() {
			$result = $this->Category->getCategoriesForAccession(0);
			$expected = array(
				3 => 'Another Ontopic',
				2 => 'Ontopic'
			);
			$this->assertEquals($result, $expected);

			$result = $this->Category->getCategoriesForAccession(1);
			$expected = array(
				3 => 'Another Ontopic',
				2 => 'Ontopic',
				4 => 'Offtopic',
				5 => 'Trash'
			);
			$this->assertEquals($result, $expected);

			$result = $this->Category->getCategoriesForAccession(2);
			$expected = array(
				1 => 'Admin',
				3 => 'Another Ontopic',
				2 => 'Ontopic',
				4 => 'Offtopic',
				5 => 'Trash'
			);
			$this->assertEquals($result, $expected);
		}

		public function testUpdateEvent() {
			$Category = $this->getMockForModel('Category', ['_dispatchEvent']);
			$Category->expects($this->once())
					->method('_dispatchEvent')
					->with('Cmd.Cache.clear', ['cache' => ['Saito', 'Thread']]);

			$data = [
				'Category' => [
					'category' => 'foo'
				]
			];
			$Category->id = 1;
			$Category->save($data);
		}

		public function testNoUpdateEvent() {
			$Category = $this->getMockForModel('Category', ['_dispatchEvent']);
			$Category->expects($this->never())
				->method('_dispatchEvent');

			$data = [
				'Category' => [
					'thread_count' => '300'
				]
			];
			$Category->id = 1;
			$Category->save($data);
		}

		public function testDeleteEvent() {
			$Category = $this->getMockForModel('Category', ['_dispatchEvent']);
			$Category->expects($this->once())
					->method('_dispatchEvent')
					->with('Cmd.Cache.clear', ['cache' => ['Saito', 'Thread']]);

			$Category->id = 1;
			$Category->delete();
		}

		public function setUp() {
			parent::setup();
			$this->Category = ClassRegistry::init('Category');
		}

		public function tearDown() {
			unset($this->Category);
			parent::tearDown();
		}

	}
