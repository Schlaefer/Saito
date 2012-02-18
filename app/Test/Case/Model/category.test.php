<?php

App::import('Model', 'Category');

class CategoryTest extends CakeTestCase {
	var $fixtures = array('app.category', 'app.user', 'app.upload', 'app.user_online', 'app.entry');

	public function testGetCategoriesForAccession() {

		// test for accession 0 (everybody)
		$result = $this->Category->getCategoriesForAccession(0);
		$expected = array ( '2' => '2', '3' => '3' );
		$this->assertEqual($result, $expected);


		// test for accession 1 (user)
		$result = $this->Category->getCategoriesForAccession(1);
		$expected = array ( '2' => '2', '3' => '3', '4' => '4', '5' => '5' );
		$this->assertEqual($result, $expected);

		// test for accession 2 (admin)
		$result = $this->Category->getCategoriesForAccession(2);
		$expected = array ( '1' => '1', '2' => '2', '3' => '3', '4' => '4', '5' => '5' );
		$this->assertEqual($result, $expected);
		}

	function startTest($message) {
		echo "<h3>Starting ".get_class($this)."->$message()</h3>\n";
		$this->Category =& ClassRegistry::init('Category');
	}

	function endTest() {
		unset($this->Category);
		ClassRegistry::flush();
	}

}
?>