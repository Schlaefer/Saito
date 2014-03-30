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

		public function testThreadMaxDepth() {
			App::uses('SaitoUser', 'Lib/SaitoUser');
			App::uses('ComponentCollection', 'Controller');
			$SaitoUser = $this->getMock(
					'SaitoUser',
					['getMaxAccession', 'getId', 'getBookmarks'],
					[new ComponentCollection]
			);
			$entry = [
					'Entry' => [
							'id' => 1,
							'tid' => 0,
							'subject' => 'a',
							'text' => 'b',
							'time' => 0,
							'fixed' => false,
							'nsfw' => false,
							'solves' => ''
					],
					'Category' => [
							'accession' => 0,
							'description' => 'd',
							'category' => 'c'
					],
					'User' => ['username' => 'u']
			];

			// root + 2 sublevels
			$entries = $entry;
			$entries['_children'] = [
					$entry + [
							'_children' => [
									$entry
							]
					]
			];

			// max depth should not apply
			Configure::write('Saito.Settings.thread_depth_indent', 9999);
			$this->EntryH->beforeRender(null);
			$result = $this->EntryH->threadCached($entries, $SaitoUser, 0);
			$this->assertEqual(substr_count($result, '<ul'), 3);

			// max depth should only allow 1 level
			Configure::write('Saito.Settings.thread_depth_indent', 2);
			$this->EntryH->beforeRender(null);
			$result = $this->EntryH->threadCached($entries, $SaitoUser, 0);
			$this->assertEqual(substr_count($result, '<ul'), 2);
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

