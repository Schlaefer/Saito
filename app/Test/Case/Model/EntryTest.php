<?php

	App::uses('Entry', 'Model');
	App::uses('ComponentCollection', 'Controller');
	App::uses('SaitoUser', 'Lib/SaitoUser');

	// @codingStandardsIgnoreStart
	class EntryMock extends Entry {

		public $_CurrentUser;

		public $_editPeriod;

		public function prepareBbcode($string) {
			return $string;
		}

		public function getSubjectMaxLength() {
			return $this->_subjectMaxLenght;
		}

	}
	// @codingStandardsIgnoreEnd

	class EntryTest extends CakeTestCase {

		public $fixtures = array(
			'app.bookmark',
			'app.ecach',
			'app.user',
			'app.user_online',
			'app.user_read',
			'app.entry',
			'app.category',
			'app.smiley',
			'app.smiley_code',
			'app.setting',
			'app.upload',
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

		public function testCreateSuccess() {
			App::uses('Category', 'Model');

			$SaitoUser = $this->getMock(
				'SaitoUser',
				['getMaxAccession', 'getId', 'getBookmarks'],
				[new ComponentCollection]
			);
			$SaitoUser->expects($this->any())
					->method('getMaxAccession')
					->will($this->returnValue(2));
			$SaitoUser->expects($this->any())
					->method('getId')
					->will($this->returnValue(1));
			$this->Entry->SharedObjects['CurrentUser'] = $SaitoUser;

			$data[$this->Entry->alias] = [
				'pid' => 0,
				// +1 because str_pad calculates non ascii chars to a string length of 2
				'subject' => str_pad('Sübject', $this->Entry->getSubjectMaxLength() + 1, '.'),
				'text' => 'Täxt',
				'category' => 1
			];

			$lastEntry = $this->Entry->find(
				'first',
				['contain' => false, 'order' => ['Entry.id' => 'DESC']]
			);
			$expectedThreadId = (int)$lastEntry['Entry']['tid'] + 1 . '';

			$result = $this->Entry->createPosting($data);

			$this->assertEmpty($this->Entry->validationErrors);

			$expected = $data;
			$expected['Entry']['tid'] = $expectedThreadId;
			$expected['Entry']['subject'] = Sanitize::html($expected['Entry']['subject']);
			$expected['Entry']['text'] = Sanitize::html($expected['Entry']['text']);
			$result = array_intersect_key($result, $expected);
			$result[$this->Entry->alias] = array_intersect_key(
				$result[$this->Entry->alias],
				$expected[$this->Entry->alias]
			);

			$this->assertEquals(
				$result,
				$expected
			);
		}

		public function testCreateCategoryThreadCounterUpdate() {
			App::uses('Category', 'Model');

			$SaitoUser = $this->getMock(
				'SaitoUser',
				['getMaxAccession', 'getId'],
				[new ComponentCollection]
			);
			$SaitoUser->expects($this->any())
					->method('getMaxAccession')
					->will($this->returnValue(2));
			$SaitoUser->expects($this->any())
					->method('getId')
					->will($this->returnValue(1));
			$this->Entry->SharedObjects['CurrentUser'] = $SaitoUser;

			Configure::write('Saito.Settings.subject_maxlength', 75);
			$this->Entry->Category = $this->getMock(
				'Category',
				['updateThreadCounter'],
				[false, 'categories', 'test']
			);
			$this->Entry->Category->expects($this->once())
					->method('updateThreadCounter')
					->will($this->returnValue(true));
			$data['Entry'] = [
				'pid' => 0,
				'subject' => 'Subject',
				'category' => 1
			];
			$this->Entry->createPosting($data);
		}

		public function testToggle() {
			$this->Entry->id = 2;

			//* test that thread is unlocked
			$result = $this->Entry->field('locked');
			$this->assertTrue($result == false);

			//* lock thread
			$this->Entry->toggle('locked');
			$result = $this->Entry->field('locked');
			$this->assertTrue($result == true);

			//* unlock thread again
			$this->Entry->toggle('locked');
			$result = $this->Entry->field('locked');
			$this->assertTrue($result == false);
		}

		public function testMergeThreadOntoItself() {
			$this->Entry->id = 2;
			$result = $this->Entry->threadMerge(1);
			$this->assertFalse($result);
		}

		/**
		 *
		 *
		 * Merge subposting 5 in thread 2 onto root-posting in thread 1
		 */
		public function testMergeSourceIsNoThreadRoot() {
			$this->Entry->id = 5;
			$result = $this->Entry->threadMerge(1);
			$this->assertFalse($result);
		}

		/**
		 * Test merge
		 *
		 * Merge thread 2 (root-id: 4) onto entry 2 in thread 1
		 */
		public function testThreadMerge() {
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
			$this->assertEquals($appendedEntry, 0);

			// count both threads
			$targetEntryCount = $this->Entry->find('count', array('conditions' => array ('tid' => '1')));
			$sourceEntryCount = $this->Entry->find('count', array('conditions' => array ('tid' => '4')));

			// do the merge
			$this->Entry->id = 4;
			$this->Entry->threadMerge(2);

			// target thread is contains now all entries
			$targetEntryCountAfterMerge = $this->Entry->find('count', array('conditions' => array ('tid' => '1')));
			$this->assertEquals($targetEntryCountAfterMerge, $sourceEntryCount + $targetEntryCount);

			//appended entries have category of target thread
			$targetCategoryCount = $this->Entry->find('count', array(
					'conditions' => array ('Entry.tid' => 1, 'Entry.category' => 2)
					));
			$this->assertEquals($targetCategoryCount, $targetEntryCount + $sourceEntryCount);

			// source thread is gone
			$sourceEntryCountAfterMerge = $this->Entry->find('count', array('conditions' => array ('tid' => '4')));
			$this->assertEquals($sourceEntryCountAfterMerge, 0);

			// entry is appended now
			$appendedEntry = $this->Entry->find(
					'count', array(
							'conditions' => array ('Entry.id' => 4, 'Entry.pid' => '2' )));
			$this->assertEquals($appendedEntry, 1);
		}

		public function testIdsForNode() {
			$expected = array(2, 3, 7, 9);
			$result = $this->Entry->getIdsForNode(2);
			$this->assertEquals(array_values($result), array_values($expected));

			$expected = array(1, 2, 3, 7, 8, 9);
			$result = $this->Entry->getIdsForNode(1);
			$this->assertEquals($result, $expected);
		}

		public function testThreadIncrementView() {
			$_tid = 4;
			$this->Entry->threadIncrementViews($_tid);
			$result = $this->Entry->find('all', [
				'contain' => false,
				'conditions' => ['Entry.tid' => $_tid],
				'fields' => ['Entry.views']
			]);
			$expected = array(
					0 => ['Entry' => ['views' => '1']],
					1 => ['Entry' => ['views' => '1']]
			);
			$this->assertEquals($result, $expected);
		}

		public function testThreadIncrementViewOmitUser() {
			$_tid = 4;
			$_userId = 3;
			$this->Entry->threadIncrementViews($_tid, $_userId);
			$result = $this->Entry->find('all', [
					'contain' => false,
					'conditions' => ['Entry.tid' => $_tid],
					'fields' => ['Entry.views']
			]);
			$expected = array(
					0 => ['Entry' => ['views' => '1']],
					1 => ['Entry' => ['views' => '0']]
			);
			$this->assertEquals($result, $expected);
		}

		public function testChangeThreadCategory() {
			$SaitoUser = $this->getMock(
				'SaitoUser',
				['getMaxAccession'],
				[new ComponentCollection]
			);
			$SaitoUser->expects($this->once())
					->method('getMaxAccession')
					->will($this->returnValue(2));
			$this->Entry->SharedObjects['CurrentUser'] = $SaitoUser;

			$_oldCategory = 2;
			$_newCategory = 1;

			$_nBeforeChange = $this->Entry->find('count', array(
				'contain' => false,
				'conditions' => array(
					'tid' => 1,
					'category' => $_oldCategory,
				)
			));
			$this->assertGreaterThan(1, $_nBeforeChange);

			$this->Entry->id = 1;
			$this->Entry->SharedObjects['CurrentUser'] = $SaitoUser;
			$this->Entry->save(['Entry' => ['category' => $_newCategory]]);

			$_nAfterChange = $this->Entry->find('count', array(
				'contain' => false,
				'conditions' => array(
					'tid' => 1,
					'category' => $_newCategory,
				)
			));
			$this->assertEquals($_nBeforeChange, $_nAfterChange);

			$_nAfterChangeOld = $this->Entry->find('count', array(
				'contain' => false,
				'conditions' => array(
					'tid' => 1,
					'category' => $_oldCategory
				)
			));
			$this->assertEquals(0, $_nAfterChangeOld);
		}

		public function testChangeThreadCategoryNotAnExistingCategory() {
			$SaitoUser = $this->getMock(
				'SaitoUser',
				['getMaxAccession'],
				[new ComponentCollection]
			);
			$SaitoUser->expects($this->once())
					->method('getMaxAccession')
					->will($this->returnValue(2));
			$this->Entry->SharedObjects['CurrentUser'] = $SaitoUser;

			$_newCategory = 9999;

			$this->Entry->id = 1;
			$this->Entry->_CurrentUser = $SaitoUser;
			$result = $this->Entry->save(['Entry' => ['category' => $_newCategory]]);
			$this->assertFalse($result);
		}

		public function testDeleteNodeCompleteThread() {
			//* test thread exists before we delete it
			$countBeforeDelete = $this->Entry->find('count',
					array('conditions' => array('tid' => '1') ) );
			$expected = 6;
			$this->assertEquals($countBeforeDelete, $expected);

			//* try to delete thread
			$this->Entry->id = 1;

			$this->Entry->Category = $this->getMock('Category',
				array('updateThreadCounter'),
				array(null, 'categories', 'test')
			);
			$this->Entry->Category->expects($this->once())
					->method('updateThreadCounter')->will($this->returnValue(true));

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
			$_deletedSubjects = 1 + count($this->Entry->findAllByTid(1));
			$this->Entry->Esevent->expects($this->exactly($_deletedSubjects))
					->method('deleteSubject');

			$allBookmarksBeforeDelete = $this->Entry->Bookmark->find('count');

			$result = $this->Entry->deleteNode();
			$this->assertTrue($result);

			$result = $this->Entry->find('count',
					array( 'conditions' => array( 'tid' => '1' ) ));
			$expected = 0;
			$this->assertEquals($result, $expected);

			// delete associated bookmarks
			$allBookmarksAfterDelete = $this->Entry->Bookmark->find('count');
			$numberOfBookmarksForTheDeletedThread = 3;
			$this->assertEquals($allBookmarksBeforeDelete - $numberOfBookmarksForTheDeletedThread,
				$allBookmarksAfterDelete);
		}

		public function testAnonymizeEntriesFromUser() {
			$this->Entry->anonymizeEntriesFromUser(3);

			$entriesBeforeActions = $this->Entry->find('count');

			// user has no entries anymore
			$expected = 0;
			$result = $this->Entry->find('count',
				array(
					'conditions' => array('Entry.user_id' => 3)
				));
			$this->assertEquals($result, $expected);

			// entries are now assigned to user_id 0
			$expected = 7;
			$result = $this->Entry->find('count',
				array(
					'conditions' => array('Entry.user_id' => 0)
				));
			$this->assertEquals($result, $expected);

			// name is removed
			$expected = 0;
			$result = $this->Entry->find('count',
				array(
					'conditions' => array('Entry.name' => 'Ulysses')
				));
			$this->assertEquals($result, $expected);

			// edited by is removed
			$expected = 0;
			$result = $this->Entry->find('count',
				array(
					'conditions' => array('Entry.edited_by' => 'Ulysses')
				));
			$this->assertEquals($result, $expected);

			// ip is removed
			$expected = 0;
			$result = $this->Entry->find('count',
				array(
					'conditions' => array('Entry.ip' => '1.1.1.1')
				));
			$this->assertEquals($result, $expected);

			// all entries are still there
			$expected = $entriesBeforeActions;
			$result = $this->Entry->find('count');
			$this->assertEquals($result, $expected);
		}

		public function testIsAnsweringForbidden() {
			$entry = array('Entry' => array('locked' => 0));
			$result = $this->Entry->isAnsweringForbidden($entry);
			$expected = false;
			$this->assertSame($result, $expected);
			$entry = array('Entry' => array('locked' => '0'));
			$result = $this->Entry->isAnsweringForbidden($entry);
			$expected = false;
			$this->assertSame($result, $expected);
			$entry = array('Entry' => array('locked' => false));
			$result = $this->Entry->isAnsweringForbidden($entry);
			$expected = false;
			$this->assertSame($result, $expected);
		}

		public function testIsEditingForbiddenSuccess() {
			$this->Entry->_editPeriod = 1200;
			$entry = array(
				'Entry' => array(
					'user_id' => 1,
					'time' => strftime("%c",
							time() - $this->Entry->_editPeriod + 1),
					'locked' => 0,
				)
			);
			$user = array(
				'id' => 1,
				'user_type' => 'user',
			);
			$SaitoUser = $this->getMock('SaitoUser', null, array(new ComponentCollection));
			$SaitoUser->set($user);
			$user = $SaitoUser;
			$result = $this->Entry->isEditingForbidden($entry, $user);
			$this->assertFalse($result);
		}

		public function testIsEditingForbiddenEmptyUser() {
			$entry = array(
				'Entry' => array(
					'user_id' => 1,
					'time' => strftime("%c",
							time() - (Configure::read('Saito.Settings.edit_period') * 60) + 1),
					'locked' => 0,
				)
			);
			$user = null;
			$SaitoUser = $this->getMock('SaitoUser', null, array(new ComponentCollection));
			$SaitoUser->set($user);
			$user = $SaitoUser;
			$result = $this->Entry->isEditingForbidden($entry, $user);
			$this->assertTrue($result);
		}

		public function testIsEditingForbiddenAnon() {
			$entry = array(
				'Entry' => array(
					'user_id' => 1,
					'time' => strftime("%c", time()),
				)
			);
			$user = array(
				'id' => null,
				'user_type' => 'anon',
			);
			$SaitoUser = $this->getMock('SaitoUser', null, array(new ComponentCollection));
			$SaitoUser->set($user);
			$user = $SaitoUser;
			$result = $this->Entry->isEditingForbidden($entry, $user);
			$this->assertTrue($result);
		}

		public function testIsEditingForbiddenWrongUser() {
			$entry = array(
				'Entry' => array(
					'user_id' => 1,
					'time' => strftime("%c", time()),
				)
			);
			$user = array(
				'id' => 2,
				'user_type' => 'user',
			);
			$SaitoUser = $this->getMock('SaitoUser', null, array(new ComponentCollection));
			$SaitoUser->set($user);
			$user = $SaitoUser;
			$result = $this->Entry->isEditingForbidden($entry, $user);
			$this->assertEquals($result, 'user');
		}

		public function testIsEditingForbiddenToLate() {
			$this->Entry->_editPeriod = 1200;
			$entry = [
				'Entry' => [
					'user_id' => 1,
					'locked' => false,
					'time' => strftime(
						"%c",
							time() - $this->Entry->_editPeriod - 1
					)
				]
			];
			$user = array(
				'id' => 1,
				'user_type' => 'user',
			);
			$SaitoUser = $this->getMock('SaitoUser', null, array(new ComponentCollection));
			$SaitoUser->set($user);
			$user = $SaitoUser;
			$result = $this->Entry->isEditingForbidden($entry, $user);
			$this->assertEquals($result, 'time');
		}

		public function testIsEditingForbiddenLocked() {
			$entry = array(
					'Entry' => array(
						'user_id' => 1,
						'time' => strftime("%c", time()),
						'locked' => 1,
					)
			);
			$user = array(
				'id' => 1,
				'user_type' => 'user',
			);
			$SaitoUser = $this->getMock('SaitoUser', null, array(new ComponentCollection));
			$SaitoUser->set($user);
			$user = $SaitoUser;
			$result = $this->Entry->isEditingForbidden($entry, $user);
			$this->assertEquals($result, 'locked');
		}

		public function testIsEditingForbiddenModToLateNotFixed() {
			Configure::write('Saito.Settings.edit_period', 20);
			$entry = array(
				'Entry' => array(
					'user_id' => 1,
					'time' => strftime("%c",
							time() - (Configure::read('Saito.Settings.edit_period') * 60) - 1),
					'fixed' => false,
				)
			);
			$user = array(
				'id' => 1,
				'user_type' => 'mod',
			);
			$SaitoUser = $this->getMock('SaitoUser', null, array(new ComponentCollection));
			$SaitoUser->set($user);
			$user = $SaitoUser;
			$result = $this->Entry->isEditingForbidden($entry, $user);
			$this->assertEquals($result, 'time');
		}

		public function testIsEditingForbiddenModToLateFixed() {
			$entry = array(
				'Entry' => array(
					'user_id' => 1,
					'time' => strftime("%c",
							time() - (Configure::read('Saito.Settings.edit_period') * 60) - 1),
					'fixed' => true,
				)
			);
			$user = array(
				'id' => 1,
				'user_type' => 'mod',
			);
			$SaitoUser = $this->getMock('SaitoUser', null, array(new ComponentCollection));
			$SaitoUser->set($user);
			$user = $SaitoUser;
			$result = $this->Entry->isEditingForbidden($entry, $user);
			$this->assertFalse($result);
		}

		public function testIsEditingForbiddenAdminToLateNotFixed() {
			$entry = array(
				'Entry' => array(
					'user_id' => 1,
					'time' => strftime("%c",
							time() - (Configure::read('Saito.Settings.edit_period') * 60) - 1),
					'fixed' => false,
				)
			);
			$user = array(
				'id' => 1,
				'user_type' => 'admin',
			);
			$SaitoUser = $this->getMock('SaitoUser',
				null,
				array(new ComponentCollection));
			$SaitoUser->set($user);
			$user = $SaitoUser;
			$result = $this->Entry->isEditingForbidden($entry, $user);
			$this->assertFalse($result);
		}

		public function testIsRoot() {
			$this->Entry->id = 8;
			$result = $this->Entry->isRoot();
			$this->assertFalse($result);

			$result = $this->Entry->isRoot(4);
			$this->assertTrue($result);

			$entry = array(
					$this->Entry->alias => array(
							'pid' => 0,
					)
			);
			$result = $this->Entry->isRoot($entry);
			$this->assertTrue($result);

			$entry = array(
					$this->Entry->alias => array(
							'pid' => 1,
					)
			);
			$result = $this->Entry->isRoot($entry);
			$this->assertFalse($result);
		}

		public function testTreeForNode() {
			$this->Entry = $this->getMock('Entry', array('getThreadId', 'treesForThreads'),
					array(false, 'entries', 'test')
			);

			$this->Entry->expects($this->once())
					->method('getThreadId')
					->with(2)
					->will($this->returnValue(1));

			$ar = array(
				0 => array(
					'Entry' => array(
						'id' => 1,
					),
					'_children' => array(
						0 =>
								array(
									'Entry' => array(
										'id' => 2,
									),
									'User'
								),
						'Entry' => array(
							'id' => 3,
						),
					)
				)
			);
			$this->Entry->expects($this->once())
					->method('treesForThreads')
					->with(array(array('id' => 1)))
					->will($this->returnValue($ar));

			$result = $this->Entry->treeForNode(2);
			$this->assertEquals($result,
				array(0 => array('Entry' => array('id' => '2'), 'User')));
		}

		public function testGetThreadId() {
			$result = $this->Entry->getThreadId(1);
			$expected = 1;
			$this->assertEquals($result, $expected);

			$result = $this->Entry->getThreadId(5);
			$expected = 4;
			$this->assertEquals($result, $expected);
		}

		public function testGetThreadIdNotFound() {
			$this->expectException('UnexpectedValueException');
			$result = $this->Entry->getThreadId(999);
		}

		public function testUpdateNoId() {
			$this->expectException('InvalidArgumentException');
			$this->Entry->update([]);
		}

		/**
		 * Throw error if entry to update does not exist.
		 *
		 * Don't accidentally `create`.
		 */
		public function testUpdateEntryDoesNotExist() {
			$this->expectException('NotFoundException');
			$this->Entry->update(['Entry' => ['id' => 999]]);
		}

		/**
		 * setUp method
		 *
		 * @return void
		 */
		public function setUp() {
			parent::setUp();
			$this->Entry = ClassRegistry::init([
				'class' => 'EntryMock',
				'alias' => 'Entry'
			]);
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
