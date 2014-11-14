<?php

	use Saito\Cache\ItemCache;

	App::uses('Controller', 'Controller');
	App::uses('View', 'View');
	App::uses('EntryHHelper', 'View/Helper');

	class EntryHHelperTest extends CakeTestCase {

		public function setUp() {
			parent::setUp();

			$Controller = new Controller();
			$View = new View($Controller);
			$View->set('LineCache', new ItemCache('test'));
			$this->EntryH = new EntryHHelper($View);

			$this->EntryH->dic = \Saito\Test\DicSetup::getNewDic();
		}

		public function tearDown() {
			parent::tearDown();
			unset($this->EntryH);
			ClassRegistry::flush();
		}

		public function testGetFastLink() {
			$this->EntryH->webroot = 'localhost/';

			//= simple test
			$entry = [
				'Entry' => [
					'id' => 3,
					'tid' => 1,
					'pid' => 1,
					'subject' => 'Subject',
					'text' => 'Text'
				]
			];
			$expected = "<a href='localhost/entries/view/3' class=''>Subject</a>";
			$result = $this->EntryH->getFastLink($entry);
			$this->assertEquals($expected, $result);

			//=  test 'class' input
			$class = 'my_test_class foo';
			$expected = "<a href='localhost/entries/view/3' class='my_test_class foo'>Subject</a>";
			$result = $this->EntryH->getFastLink($entry, array( 'class' => $class ));
			$this->assertEquals($expected, $result);

			//* test n/t posting
			$entry['Entry']['text'] = '';
			$expected = "<a href='localhost/entries/view/3' class=''>Subject n/t</a>";
			$result = $this->EntryH->getFastLink($entry);
			$this->assertEquals($expected, $result);
		}

	}

