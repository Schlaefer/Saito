<?php

	App::uses('Controller', 'Controller');
	App::uses('View', 'View');
	App::uses('EntryHHelper', 'View/Helper');
	App::uses('SaitoUser', 'Lib');

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

		public function testIsRepostable() {
			$edit_period = Configure::read('Saito.Settings.edit_period');
			Configure::write('Saito.Settings.edit_period', 20);

			$time = strftime('%c', time() - (20 * 60) - 1);
			$entry = array(
					'Entry' => array(
							'pid'					 => 0,
							'time'				 => $time,
							'last_answer'	 => $time,
							'reposts'			 => 0,
							'user_id'			 => 1,
					)
			);

			$SaitoUser = $this->getMock('SaitoUser', null, array(new ComponentCollection));
			$SaitoUser->set(array('id' => 1));

			$result = $this->EntryH->isRepostable($entry, $SaitoUser);

			$this->assertTrue($result);
			Configure::write('Saito.Settings.edit_period', $edit_period);
		}

		public function testIsRepostableNotUser() {
			$edit_period = Configure::read('Saito.Settings.edit_period');
			Configure::write('Saito.Settings.edit_period', 20);

			$time = strftime('%c', time() - (20 * 60) - 1);
			$entry = array(
					'Entry' => array(
							'pid'					 => 0,
							'time'				 => $time,
							'last_answer'	 => $time,
							'reposts'			 => 0,
							'user_id'			 => 2,
					)
			);

			$SaitoUser = $this->getMock('SaitoUser', null, array(new ComponentCollection));
			$SaitoUser->set(array('id' => 1));

			$result = $this->EntryH->isRepostable($entry, $SaitoUser);

			$this->assertFalse($result);
			Configure::write('Saito.Settings.edit_period', $edit_period);
		}

		public function testIsRepostableMaxTime() {
			$edit_period = Configure::read('Saito.Settings.edit_period');
			Configure::write('Saito.Settings.edit_period', 20);

			$time = strftime('%c', time() + 175860 + 1);
			$entry = array(
					'Entry' => array(
							'pid'					 => 0,
							'time'				 => $time,
							'last_answer'	 => $time,
							'reposts'			 => 0,
							'user_id'			 => 1,
					)
			);

			$SaitoUser = $this->getMock('SaitoUser', null, array(new ComponentCollection));
			$SaitoUser->set(array('id' => 1));

			$result = $this->EntryH->isRepostable($entry, $SaitoUser);

			$this->assertFalse($result);
			Configure::write('Saito.Settings.edit_period', $edit_period);
		}

		public function testIsRepostableMinTime() {
			$edit_period = Configure::read('Saito.Settings.edit_period');
			Configure::write('Saito.Settings.edit_period', 20);

			$time = strftime('%c', time() - (20 * 60) + 1);
			$entry = array(
					'Entry' => array(
							'pid'					 => 0,
							'time'				 => $time,
							'last_answer'	 => $time,
							'reposts'			 => 0,
							'user_id'			 => 1,
					)
			);

			$SaitoUser = $this->getMock('SaitoUser', null, array(new ComponentCollection));
			$SaitoUser->set(array('id' => 1));

			$result = $this->EntryH->isRepostable($entry, $SaitoUser);

			$this->assertFalse($result);
			Configure::write('Saito.Settings.edit_period', $edit_period);
		}

		public function testIsRespostableCounter() {
			$edit_period = Configure::read('Saito.Settings.edit_period');
			Configure::write('Saito.Settings.edit_period', 20);

			$time = strftime('%c', time() - (20 * 60) - 1);
			$entry = array(
					'Entry' => array(
							'pid'					 => 0,
							'time'				 => $time,
							'last_answer'	 => $time,
							'reposts'			 => 3,
							'user_id'			 => 1,
					)
			);

			$SaitoUser = $this->getMock('SaitoUser', null, array(new ComponentCollection));
			$SaitoUser->set(array('id' => 1));

			$result = $this->EntryH->isRepostable($entry, $SaitoUser);

			$this->assertFalse($result);
			Configure::write('Saito.Settings.edit_period', $edit_period);
		}

		public function testIsRespostableNotRootEntry() {
			$edit_period = Configure::read('Saito.Settings.edit_period');
			Configure::write('Saito.Settings.edit_period', 20);

			$time = strftime('%c', time() - (20 * 60) - 1);
			$entry = array(
					'Entry' => array(
							'pid'					 => 1,
							'time'				 => $time,
							'last_answer'	 => $time,
							'reposts'			 => 0,
							'user_id'			 => 1,
					)
			);

			$SaitoUser = $this->getMock('SaitoUser', null, array(new ComponentCollection));
			$SaitoUser->set(array('id' => 1));

			$result = $this->EntryH->isRepostable($entry, $SaitoUser);

			$this->assertFalse($result);
			Configure::write('Saito.Settings.edit_period', $edit_period);
		}

		public function testIsRespostableNoAnswer() {
			$edit_period = Configure::read('Saito.Settings.edit_period');
			Configure::write('Saito.Settings.edit_period', 20);

			$time = strftime('%c', time() - (20 * 60) - 1);
			$entry = array(
					'Entry' => array(
							'pid'					 => 0,
							'time'				 => $time,
							'last_answer'	 => $time + 1,
							'reposts'			 => 0,
							'user_id'			 => 1,
					)
			);

			$SaitoUser = $this->getMock('SaitoUser', null, array(new ComponentCollection));
			$SaitoUser->set(array('id' => 1));

			$result = $this->EntryH->isRepostable($entry, $SaitoUser);

			$this->assertFalse($result);
			Configure::write('Saito.Settings.edit_period', $edit_period);
		}

	}