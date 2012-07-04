<?php

	App::uses('Category', 'Model');

	class CategoryTest extends CakeTestCase {

		public $fixtures = array( 'app.category', 'app.user', 'app.upload', 'app.user_online', 'app.entry' );

		public function testGetCategoriesForAccession() {

			// test for accession 0 (everybody)
			$result = $this->Category->getCategoriesForAccession(0);
			$expected = array( '2' => '2', '3' => '3' );
			$this->assertEqual($result, $expected);


			// test for accession 1 (user)
			$result = $this->Category->getCategoriesForAccession(1);
			$expected = array( '2' => '2', '3' => '3', '4' => '4', '5' => '5' );
			$this->assertEqual($result, $expected);

			// test for accession 2 (admin)
			$result = $this->Category->getCategoriesForAccession(2);
			$expected = array( '1' => '1', '2' => '2', '3' => '3', '4' => '4', '5' => '5' );
			$this->assertEqual($result, $expected);
		}

		public function setUp() {
			parent::setup();
			$this->Category = ClassRegistry::init('Category');
		}

		public function tearDown(){
			unset($this->Category);
			parent::tearDown();
		}

	}

?>