<?php
/* EntryH Test cases generated on: 2010-07-17 15:07:14 : 1279373414*/
App::import('Helper', 'EntryH');

class EntryHHelperTestCase extends CakeTestCase {
	function startTest($message) {
		echo "<h3>Starting ".get_class($this)."->$message()</h3>\n";
		$this->EntryH =& new EntryHHelper();
	}

	function endTest() {
		unset($this->EntryH);
		ClassRegistry::flush();
	}

	function testIsAnsweringForbidden() {

		$result = $this->EntryH->isAnsweringForbidden();
		$expected = true;
		$this->assertIdentical($result, $expected);

		$entry = array( 'Entry' => array ( 'locked'	=> 0));
		$result = $this->EntryH->isAnsweringForbidden($entry);
		$expected = false;
		$this->assertIdentical($result, $expected);

		$entry = array( 'Entry' => array ( 'locked'	=> '0'));
		$result = $this->EntryH->isAnsweringForbidden($entry);
		$expected = false;
		$this->assertIdentical($result, $expected);

		$entry = array( 'Entry' => array ( 'locked'	=> false));
		$result = $this->EntryH->isAnsweringForbidden($entry);
		$expected = false;
		$this->assertIdentical($result, $expected);
	}

	public function testGetFastLink() {
		
		$this->EntryH->webroot = 'localhost/';

		//*
		$entry = array('Entry' => array(
					'id'	=> 3,
					'subject'	=> 'Subject',
					'text'		=> 'Text',
					'nsfw'		=> false,
				)
			);
		$expected = "<a href='localhost/entries/view/3' class=''>Subject</a>";
		$result = $this->EntryH->getFastLink($entry);
		$this->assertEqual($expected, $result);

		//* test n/t posting
		$entry = array('Entry' => array(
					'id'	=> 1,
					'subject'	=> 'Subject',
					'text'		=> '',
					'nsfw'		=> false,
				)
			);
		$expected = "<a href='localhost/entries/view/1' class=''>Subject n/t</a>";
		$result = $this->EntryH->getFastLink($entry);
		$this->assertEqual($expected, $result);

		//*  test 'class' input
		$entry = array('Entry' => array(
					'id'	=> 3,
					'subject'	=> 'Subject',
					'text'		=> 'Text',
					'nsfw'		=> false,
				)
			);
		$class = 'my_test_class foo';
		$expected = "<a href='localhost/entries/view/3' class='my_test_class foo'>Subject</a>";
		$result = $this->EntryH->getFastLink($entry, array('class'=>$class));
		$this->assertEqual($expected, $result);

	}

}
?>