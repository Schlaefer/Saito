<?php

	App::uses('Entry', 'Model');

	class EntryTest extends CakeTestCase {

		public $fixtures = array(
				'app.bookmark',
				'app.ecach',
				'app.user', 'app.user_online', 'app.entry', 'app.category',
				'app.smiley', 'app.smiley_code', 'app.setting', 'app.upload',
				'app.esevent',
				'app.esnotification',
				);

		public function testBeforeValidate() {

			//* save entry with text
			$entry['Entry'] = array(
					'user_id' => 3,
					'subject' => 'Test Subject',
					'Text' => 'Text Text',
					'pid' => '2',
			);
		}

    public function testCreate() {
      App::uses('Category', 'Model');

      Configure::write('Saito.Settings.subject_maxlength', 75);
      $this->Entry->Category = $this->getMock('Category', array('updateThreadCounter'), array(false, 'category', 'test'));
      $this->Entry->Category->expects($this->once())->method('updateThreadCounter')->will($this->returnValue(true));
      $data['Entry'] = array(
          'pid' => 0,
          'subject' => 'Subject',
          'category'  => 1,
          'user_id'   => 1,
      );
      $this->Entry->createPosting($data);

    }

		public function testToggle() {

			$this->Entry->id = 2;

			//* test that thread is unlocked
			$result = $this->Entry->field('locked');
			$this->assertTrue($result == FALSE);

			//* lock thread
			$this->Entry->toggle('locked');
			$result = $this->Entry->field('locked');
			$this->assertTrue($result == TRUE);

			//* unlock thread again
			$this->Entry->toggle('locked');
			$result = $this->Entry->field('locked');
			$this->assertTrue($result == FALSE);
		}

		public function testMergeThreadOntoItself() {
			$this->Entry->id = 2;
			$result = $this->Entry->merge(1);
			$this->assertFalse($result);
		}

		public function testMergeSourceIsNoThreadRoot() {
			$this->Entry->id = 5;
			$result = $this->Entry->merge(1);
//			$this->assertFalse($result);
		}

		public function testMerge() {

			// notifications must be merged
			App::uses('Esevent', 'Model');
			$this->Entry->Esevent = $this->getMock(
					'Esevent', array('transferSubjectForEventType'), array(null, 'esevents', 'test'));
			$this->Entry->Esevent->expects($this->once())
					->method('transferSubjectForEventType')
					->with(4, 1, 'thread');

			// entry is not appended yet
			$appendedEntry = $this->Entry->find(
					'count', array(
							'conditions' => array ('Entry.id' => 4, 'Entry.pid' => 2 )));
			$this->assertEqual($appendedEntry, 0);

			// count both threads
			$targetEntryCount = $this->Entry->find('count', array('conditions' => array ('tid' => '1')));
			$sourceEntryCount = $this->Entry->find('count', array('conditions' => array ('tid' => '4')));

			// do the merge
			$this->Entry->id = 4;
			$this->Entry->merge(2);

			// target thread is contains now all entries
			$targetEntryCountAfterMerge = $this->Entry->find('count', array('conditions' => array ('tid' => '1')));
			$this->assertEqual($targetEntryCountAfterMerge, $sourceEntryCount + $targetEntryCount);

			// source thread is gone
			$sourceEntryCountAfterMerge = $this->Entry->find('count', array('conditions' => array ('tid' => '4')));
			$this->assertEqual($sourceEntryCountAfterMerge, 0);

			// entry is appended now
			$appendedEntry = $this->Entry->find(
					'count', array(
							'conditions' => array ('Entry.id' => 4, 'Entry.pid' => '2' )));
			$this->assertEqual($appendedEntry, 1);

		}

		public function testDeleteTree() {

			//* test thread exists before we delete it
			$result = $this->Entry->find('count',
					array( 'conditions' => array( 'tid' => '1' ) ));
			$expected = 3;
			$this->assertEqual($result, $expected);

			//* try to delete subentry
			$this->Entry->id = 2;
			$result = $this->Entry->deleteTree();
			$this->assertFalse($result);

			$result = $this->Entry->find('count',
					array( 'conditions' => array( 'tid' => '1' ) ));
			$expected = 3;
			$this->assertEqual($result, $expected);

			//* try to delete thread

			$this->Entry->id = 1;

      $this->Entry->Category = $this->getMock('Category', array('updateThreadCounter'),
					array(null, 'categories', 'test')
					);
      $this->Entry->Category->
          expects($this->once())->method('updateThreadCounter')->will($this->returnValue(true));

			// test that event and notifications are deleted
			App::uses('Esevent', 'Model');
			$this->Entry->Esevent = $this->getMock(
					'Esevent', array('deleteSubject'), array(null, 'esevents', 'test'));

			// delete thread
			$this->Entry->Esevent->expects($this->at(0))
					->method('deleteSubject')
					->with(1, 'thread');
			// delete first entry
			$this->Entry->Esevent->expects($this->at(1))
					->method('deleteSubject')
					->with(1, 'entry');
			// delete sum: 1 thread + all entries in thread
			$deleted_subjects = 1 + count($this->Entry->findAllByTid(1));
			$this->Entry->Esevent->expects($this->exactly($deleted_subjects))
					->method('deleteSubject');

			$allBookmarksBeforeDelete = $this->Entry->Bookmark->find('count');

			$result = $this->Entry->deleteTree();
			$this->assertTrue($result);

			$result = $this->Entry->find('count',
					array( 'conditions' => array( 'tid' => '1' ) ));
			$expected = 0;
			$this->assertEqual($result, $expected);

			// delete associated bookmarks
			$allBookmarksAfterDelete = $this->Entry->Bookmark->find('count');
			$numberOfBookmarksForTheDeletedThread = 3;
      $this->assertEqual($allBookmarksBeforeDelete - $numberOfBookmarksForTheDeletedThread, $allBookmarksAfterDelete);
		}

    public function testAnonymizeEntriesFromUser() {
      $this->Entry->anonymizeEntriesFromUser(3);

      $entriesBeforeActions = $this->Entry->find('count');

      // user has no entries anymore
      $expected = 0;
      $result = $this->Entry->find('count', array(
          'conditions' => array ('Entry.user_id' => 3)
      ));
      $this->assertEqual($result, $expected);

      // entries are now assigned to user_id 0
      $expected = 3;
      $result = $this->Entry->find('count', array(
          'conditions' => array ('Entry.user_id' => 0)
      ));
      $this->assertEqual($result, $expected);

      // name is removed
      $expected = 0;
      $result = $this->Entry->find('count', array(
          'conditions' => array ('Entry.name' => 'Ulysses')
      ));
      $this->assertEqual($result, $expected);

      // edited by is removed
      $expected = 0;
      $result = $this->Entry->find('count', array(
          'conditions' => array ('Entry.edited_by' => 'Ulysses')
      ));
      $this->assertEqual($result, $expected);

      // ip is removed
      $expected = 0;
      $result = $this->Entry->find('count', array(
          'conditions' => array ('Entry.ip' => '1.1.1.1')
      ));
      $this->assertEqual($result, $expected);


      // all entries are still there
      $expected = $entriesBeforeActions;
      $result = $this->Entry->find('count');
      $this->assertEqual($result, $expected);

    }

		public function testIsAnsweringForbidden() {
			$result = $this->Entry->isAnsweringForbidden();
			$expected = true;
			$this->assertSame($result, $expected);
			$entry = array('Entry' => array('locked'	 => 0));
			$result = $this->Entry->isAnsweringForbidden($entry);
			$expected = false;
			$this->assertSame($result, $expected);
			$entry = array('Entry' => array('locked'	 => '0'));
			$result = $this->Entry->isAnsweringForbidden($entry);
			$expected = false;
			$this->assertSame($result, $expected);
			$entry = array('Entry' => array('locked'	 => false));
			$result = $this->Entry->isAnsweringForbidden($entry);
		$expected = false;
			$this->assertSame($result, $expected);
		}

		public function testIsEditingForbiddenSuccess() {
			$entry = array(
					'Entry' => array(
							'user_id'	 => 1,
							'time'		 => strftime("%c",
									time() - (Configure::read('Saito.Settings.edit_period') * 60 ) + 1),
							'locked'	 => 0,
					)
			);
			$user = array(
					'id'				 => 1,
					'user_type'	 => 'user',
			);
			$result = $this->Entry->isEditingForbidden($entry, $user);
			$this->assertFalse($result);
		}

		public function testIsEditingForbiddenEmptyUser() {
			$entry = array(
					'Entry' => array(
							'user_id'	 => 1,
							'time'		 => strftime("%c",
									time() - (Configure::read('Saito.Settings.edit_period') * 60 ) + 1),
							'locked'	 => 0,
					)
			);
			$user = null;
			$result = $this->Entry->isEditingForbidden($entry, $user);
			$this->assertTrue($result);
		}


		public function testIsEditingForbiddenAnon() {
			$entry = array(
					'Entry' => array(
							'user_id'	 => 1,
							'time'		 => strftime("%c", time()),
					)
			);
			$user = array(
					'id'				 => null,
					'user_type'	 => 'anon',
			);
			$result = $this->Entry->isEditingForbidden($entry, $user);
			$this->assertTrue($result);
		}

		public function testIsEditingForbiddenWrongUser() {
			$entry = array(
					'Entry' => array(
							'user_id'	 => 1,
							'time'		 => strftime("%c", time()),
					)
			);
			$user = array(
					'id'				 => 2,
					'user_type'	 => 'user',
			);
			$result = $this->Entry->isEditingForbidden($entry, $user);
			$this->assertEqual($result, 'user');
		}

		public function testIsEditingForbiddenToLate() {
			$entry = array(
					'Entry' => array(
							'user_id'	 => 1,
							'time'		 => strftime("%c",
									time() - (Configure::read('Saito.Settings.edit_period') * 60 ) - 1),
					)
			);
			$user = array(
					'id'				 => 1,
					'user_type'	 => 'user',
			);
			$result = $this->Entry->isEditingForbidden($entry, $user);
			$this->assertEqual($result, 'time');
		}

		public function testIsEditingForbiddenLocked() {
			$entry = array(
					'Entry' => array(
							'user_id'	 => 1,
							'time'		 => strftime("%c", time()),
							'locked'	 => 1,
					)
			);
			$user = array(
					'id'				 => 1,
					'user_type'	 => 'user',
			);
			$result = $this->Entry->isEditingForbidden($entry, $user);
			$this->assertEqual($result, 'locked');
		}

		public function testIsEditingForbiddenModToLateNotFixed() {
			$entry = array(
					'Entry' => array(
							'user_id'	 => 1,
							'time'		 => strftime("%c",
									time() - (Configure::read('Saito.Settings.edit_period') * 60 ) - 1),
							'fixed'		 => false,
					)
			);
			$user = array(
					'id'				 => 1,
					'user_type'	 => 'mod',
			);
			$result = $this->Entry->isEditingForbidden($entry, $user);
			$this->assertEqual($result, 'time');
		}

		public function testIsEditingForbiddenModToLateFixed() {
			$entry = array(
					'Entry' => array(
							'user_id'	 => 1,
							'time'		 => strftime("%c",
									time() - (Configure::read('Saito.Settings.edit_period') * 60 ) - 1),
							'fixed'		 => true,
					)
			);
			$user = array(
					'id'				 => 1,
					'user_type'	 => 'mod',
			);
			$result = $this->Entry->isEditingForbidden($entry, $user);
			$this->assertFalse($result);
		}

		public function testIsEditingForbiddenAdminToLateNotFixed() {
			$entry = array(
					'Entry' => array(
							'user_id'	 => 1,
							'time'		 => strftime("%c",
									time() - (Configure::read('Saito.Settings.edit_period') * 60 ) - 1),
							'fixed'		 => false,
					)
			);
			$user = array(
					'id'				 => 1,
					'user_type'	 => 'admin',
			);
			$result = $this->Entry->isEditingForbidden($entry, $user);
			$this->assertFalse($result);
		}

		public function testIsEditingForbiddenMockUserType() {
			$this->Entry = $this->getMock('Entry', array('isEditingForbidden'));

			$entry = array(
					'Entry' => array(
							'user_id'	 => 1,
							'time'		 => strftime("%c",
									time() - (Configure::read('Saito.Settings.edit_period') * 60 ) + 1),
							'locked'	 => 0,
					)
			);
			$user = array(
					'id'				 => 1,
					'user_type'	 => 'admin',
			);
			$user_mock = $user;
			$user_mock['user_type'] = 'user';

			$this->Entry->expects($this->once())
					->method('isEditingForbidden')
					->with($entry, $user_mock);
			$this->Entry->isEditingForbiddenMockUserType($entry, $user, 'user');
		}

		/**
     * setUp method
     *
     * @return void
     */
    public function setUp() {
      parent::setUp();
      $this->Entry = ClassRegistry::init('Entry');
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown() {
      unset($this->Entry);

      parent::tearDown();
    }

  }

?>