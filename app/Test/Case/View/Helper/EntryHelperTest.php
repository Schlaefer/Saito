<?php

	App::uses('Controller', 'Controller');
	App::uses('View', 'View');
	App::uses('EntryHHelper', 'View/Helper');

	class EntryHHelperTest extends CakeTestCase {

		public function setUp() {
			parent::setUp();

			$Controller = new Controller();
			$View = new View($Controller);
			$this->EntryH = new EntryHHelper($View);
		}

		public function tearDown() {
			parent::tearDown();
			unset($this->EntryH);
			ClassRegistry::flush();
		}

		public function testGetFastLink() {
			$this->EntryH->webroot = 'localhost/';

			//*
			$entry = array( 'Entry' => array(
							'id' => 3,
							'subject' => 'Subject',
							'text' => 'Text',
							'nsfw' => false,
					)
			);
			$expected = "<a href='localhost/entries/view/3' class=''>Subject</a>";
			$result = $this->EntryH->getFastLink($entry);
			$this->assertEqual($expected, $result);

			//* test n/t posting
			$entry = array( 'Entry' => array(
							'id' => 1,
							'subject' => 'Subject',
							'text' => '',
							'nsfw' => false,
					)
			);
			$expected = "<a href='localhost/entries/view/1' class=''>Subject n/t</a>";
			$result = $this->EntryH->getFastLink($entry);
			$this->assertEqual($expected, $result);

			//*  test 'class' input
			$entry = array( 'Entry' => array(
							'id' => 3,
							'subject' => 'Subject',
							'text' => 'Text',
							'nsfw' => false,
					)
			);
			$class = 'my_test_class foo';
			$expected = "<a href='localhost/entries/view/3' class='my_test_class foo'>Subject</a>";
			$result = $this->EntryH->getFastLink($entry, array( 'class' => $class ));
			$this->assertEqual($expected, $result);
		}

	}

