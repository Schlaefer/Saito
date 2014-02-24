<?php

	App::uses('Controller', 'Controller');
	App::uses('EntriesController', 'Controller');
	App::uses('SaitoControllerTestCase', 'Lib');

	class EntriesMockController extends EntriesController {

		// @codingStandardsIgnoreStart
		public $uses = array('Entry');
		// @codingStandardsIgnoreEnd

		public function getInitialThreads($User, $order = 'Entry.last_answer DESC') {
			$this->_getInitialThreads($User, $order);
		}

		public function searchStringSanitizer($string) {
			return $this->_searchStringSanitizer($string);
		}

	}

	class EntriesControllerTestCase extends SaitoControllerTestCase {

		public $fixtures = [
			'app.bookmark',
			'app.category',
			'app.ecach',
			'app.entry',
			'app.esevent',
			'app.esnotification',
			'app.setting',
			'app.shout',
			'app.smiley',
			'app.smiley_code',
			'app.upload',
			'app.user',
			'app.user_online'
		];

		public function testMix() {
			$result = $this->testAction('/entries/mix/1', array('return' => 'vars'));
			$this->assertStringStartsWith('First_Subject',
				$result['title_for_layout']);
		}

		public function testMixNotFound() {
			$Entries = $this->generate('Entries', array());
			$this->expectException('NotFoundException');
			$this->testAction('/entries/mix/9999');
		}

		/**
		 * only logged in users should be able to answer
		 */
		public function testAddUserNotLoggedInGet() {
			$this->generate('Entries', ['methods' => 'add']);
			$this->testAction('/entries/add');
			$this->assertRedirectedTo('login');
		}

		/**
		 * successfull add request
		 */
		public function testAddSuccess() {
			//* setup mock and data
			$C = $this->generate(
				'Entries',
				['models' => ['Esevent' => ['notifyUserOnEvents']]]
			);
			$this->_loginUser(1);
			$data = [
				'Entry' => [
					'subject' => 'subject',
					'text' => 'text',
					'category' => 1,
				],
				'Event' => [
					1 => ['event_type_id' => 0],
					2 => ['event_type_id' => 1]
				]
			];

			$latestEntry = $C->Entry->find('first',
					['contain' => false, 'order' => ['Entry.id' => 'desc']]);
			$expectedId = $latestEntry['Entry']['id'] + 1;

			//* setup notification test
			$expected = [
				[
					'subject' => $expectedId,
					'event' => 'Model.Entry.replyToEntry',
					'receiver' => 'EmailNotification',
					'set' => 0,
				],
				[
					'subject' => $expectedId,
					'event' => 'Model.Entry.replyToThread',
					'receiver' => 'EmailNotification',
					'set' => 1,
				]
			];
			$C->Entry->Esevent->expects($this->once())
					->method('notifyUserOnEvents')
					->with(1, $expected);

			//* test
			$this->testAction(
				'entries/add',
				[
					'data' => $data,
					'method' => 'POST',
					'return' => 'vars'
				]
			);
		}

		public function testNoDirectCallOfAnsweringFormWithId() {
			$Entries = $this->generate('Entries',
				array(
					'methods' => array('referer')
				));
			$this->_loginUser(1);
			$Entries->expects($this->once())
					->method('referer')
					->will($this->returnValue('/foo'));
			$this->testAction('/entries/add/1');
			$this->assertRedirectedTo('foo');
		}

		/**
		 * User is not logged in
		 */
		public function testCategoryChooserNotLoggedIn() {
			$Entries = $this->generate('EntriesMock',
				array(
					'methods' => array(
						'paginate',
					),
					'models' => array(
						'Category' => array('getCategoriesForAccession'),
						'User' => array('getMaxAccession'),
					)
				));

			Configure::write('Saito.Settings.category_chooser_global', 1);

			$Entries->expects($this->once())
					->method('paginate')
					->will($this->returnValue(array()));

			$Entries->Entry->Category->expects($this->exactly(1))
					->method('getCategoriesForAccession')
					->will($this->returnValue(array(
							1 => '1',
							2 => '2',
							7 => '7'
						)
					));

			App::uses('CurrentUserComponent', 'Controller/Component');
			App::uses('ComponentCollection', 'Controller');
			$User = new CurrentUserComponent(new ComponentCollection());
			$User->set(array());
			$Entries->getInitialThreads($User);
			$this->assertFalse(isset($Entries->viewVars['categoryChooser']));
		}

		/**
		 * Admin completely deactivated category-chooser
		 */
		public function testCategoryChooserDeactivated() {
			$Entries = $this->generate('EntriesMock',
				array(
					'methods' => array(
						'paginate',
					),
					'models' => array(
						'Category' => array('getCategoriesForAccession'),
						'User' => array('getMaxAccession'),
					)
				));

			Configure::write('Saito.Settings.category_chooser_global', 0);
			Configure::write('Saito.Settings.category_chooser_user_override', 0);

			$Entries->expects($this->once())
					->method('paginate')
					->will($this->returnValue(array()));

			$Entries->Entry->Category->expects($this->exactly(1))
					->method('getCategoriesForAccession')
					->will($this->returnValue(array(
							1 => '1',
							2 => '2',
							7 => '7'
						)
					));

			App::uses('CurrentUserComponent', 'Controller/Component');
			App::uses('ComponentCollection', 'Controller');
			$User = new CurrentUserComponent(new ComponentCollection());
			$User->set(array(
				'id' => 1,
				'user_sort_last_answer' => 1,
				'user_type' => 'admin',
				'user_category_active' => 0,
				'user_category_custom' => '',
				'user_category_override' => 1,
			));
			$Entries->getInitialThreads($User);
			$this->assertFalse(isset($Entries->viewVars['categoryChooser']));
		}

		public function testCategoryChooserEmptyCustomSet() {
			$Entries = $this->generate('EntriesMock',
				array(
					'methods' => array(
						'paginate',
					),
					'models' => array(
						'Category' => array('getCategoriesForAccession'),
						'User' => array('getMaxAccession'),
					)
				));

			Configure::write('Saito.Settings.category_chooser_global', 0);
			Configure::write('Saito.Settings.category_chooser_user_override', 1);

			$Entries->expects($this->once())
					->method('paginate')
					->will($this->returnValue(array()));

			$Entries->Entry->Category->expects($this->exactly(1))
					->method('getCategoriesForAccession')
					->will($this->returnValue(array(
							1 => '1',
							2 => '2',
							7 => '7'
						)
					));

			App::uses('CurrentUserComponent', 'Controller/Component');
			App::uses('ComponentCollection', 'Controller');
			$User = new CurrentUserComponent(new ComponentCollection());
			$User->set(array());
			$User->set(array(
				'id' => 1,
				'user_sort_last_answer' => 1,
				'user_type' => 'admin',
				'user_category_active' => 0,
				'user_category_custom' => array(),
				'user_category_override' => 1,
			));
			$Entries->getInitialThreads($User);
			$this->assertTrue(isset($Entries->viewVars['categoryChooser']));
			$this->assertEqual($Entries->viewVars['categoryChooserTitleId'], 'All Categories');
		}

		/**
		 * Test custom set
		 *
		 * - new categories (8) are in the custom set
		 */
		public function testCategoryChooserCustomSet() {
			$Entries = $this->generate('EntriesMock',
				array(
					'methods' => array(
						'paginate',
					),
					'models' => array(
						'Category' => array(
							'getCategoriesForAccession',
							'getCategoriesSelectForAccession'
						),
						'User' => array('getMaxAccession'),
					)
				));

			Configure::write('Saito.Settings.category_chooser_global', 1);

			$Entries->expects($this->once())
					->method('paginate')
					->will($this->returnValue(array()));

			$Entries->Entry->Category->expects($this->once())
					->method('getCategoriesForAccession')
					->will($this->returnValue(array(
							2 => '2',
							7 => '7',
							8 => '8'
						)
					));
			$Entries->Entry->Category->expects($this->once())
					->method('getCategoriesSelectForAccession')
					->will($this->returnValue(array(
							2 => 'Ontopic',
							7 => 'Foo',
							8 => 'Bar'
						)
					));

			App::uses('CurrentUserComponent', 'Controller/Component');
			App::uses('ComponentCollection', 'Controller');
			$User = new CurrentUserComponent(new ComponentCollection());
			$User->set(array());
			$User->set(array(
				'id' => 1,
				'user_sort_last_answer' => 1,
				'user_type' => 'admin',
				'user_category_active' => 0,
				'user_category_custom' => array(1 => 1, 2 => 1, 7 => 0),
			));
			$Entries->getInitialThreads($User);
			$this->assertTrue(isset($Entries->viewVars['categoryChooser']));
			$this->assertEqual($Entries->viewVars['categoryChooserChecked'],
				array(
					'2' => '2',
					'8' => '8',
				));
			$this->assertEqual($Entries->viewVars['categoryChooser'],
				array(
					'2' => 'Ontopic',
					'7' => 'Foo',
					'8' => 'Bar',
				));
			$this->assertEqual($Entries->viewVars['categoryChooserTitleId'],
				'Custom');
		}

		public function testCategoryChooserSingleCategory() {
			$Entries = $this->generate('EntriesMock',
				array(
					'methods' => array(
						'paginate',
					),
					'models' => array(
						'Category' => array('getCategoriesForAccession'),
						'User' => array('getMaxAccession'),
					)
				));

			Configure::write('Saito.Settings.category_chooser_global', 1);

			$Entries->expects($this->once())
					->method('paginate')
					->will($this->returnValue(array()));

			$Entries->Entry->Category->expects($this->exactly(1))
					->method('getCategoriesForAccession')
					->will($this->returnValue(array(
							1 => '1',
							2 => '2',
							7 => '7'
						)
					));

			App::uses('CurrentUserComponent', 'Controller/Component');
			App::uses('ComponentCollection', 'Controller');
			$User = new CurrentUserComponent(new ComponentCollection());
			$User->set(array());
			$User->set(array(
				'id' => 1,
				'user_sort_last_answer' => 1,
				'user_type' => 'admin',
				'user_category_active' => 7,
				'user_category_custom' => array(1 => 1, 2 => 1, 7 => 0),
			));
			$Entries->getInitialThreads($User);
			$this->assertTrue(isset($Entries->viewVars['categoryChooser']));
			$this->assertEqual($Entries->viewVars['categoryChooserTitleId'], 7);
			$this->assertEqual($Entries->viewVars['categoryChooserChecked'],
				array(
					'1' => '1',
					'2' => '2',
				));
		}

		public function testIndex() {
			$this->generate('Entries');
			$this->_logoutUser();

			//* not logged in user
			$result = $this->testAction('/entries/index', array('return' => 'vars'));
			$entries = $result['entries'];
			$this->assertEqual(count($entries), 3);

			//* logged in user
			$this->_loginUser(3);
			$result = $this->testAction('/entries/index', array('return' => 'vars'));
			$entries = $result['entries'];
			$this->assertEqual(count($entries), 4);
		}

		public function testIndexSanitation() {
			$this->generate('Entries');
			$this->_loginUser(7);

			// uses contents to check in slidetabs
			$result = $this->testAction('/entries/index', ['return' => 'contents']);
			// uses <body>-HTML only: exclude <head> which may contain unescaped JS-data
			preg_match('/<body(.*)<\/body>/sm', $result, $matches);
			$result = $matches[0];
			$this->assertTextNotContains('&<Subject', $result);
			$this->assertTextContains('&amp;&lt;Subject', $result);
			$this->assertTextNotContains('&<Username', $result);
			$this->assertTextContains('&amp;&lt;Username', $result);
			// check for no double encoding
			$this->assertTextNotContains('&amp;amp;&amp;lt;Username', $result);
		}

		public function testMergeNoSourceId() {
			$Entries = $this->generate('Entries',
				array(
					'models' => array(
						'Entry' => array('merge')
					)
				));
			$this->_loginUser(2);

			$data = array(
				'Entry' => array(
					'targetId' => 2,
				)
			);

			$Entries->Entry->expects($this->never())
					->method('merge');
			$this->expectException('NotFoundException');
			$result = $this->testAction('/entries/merge/',
				array(
					'data' => $data,
					'method' => 'post'
				));
		}

		public function testMergeSourceIdNotFound() {
			$Entries = $this->generate('Entries',
				array(
					'models' => array(
						'Entry' => array('merge')
					)
				));
			$this->_loginUser(2);

			$data = array(
				'Entry' => array(
					'targetId' => 2,
				)
			);

			$Entries->Entry->expects($this->never())
					->method('merge');
			$this->expectException('NotFoundException');
			$result = $this->testAction('/entries/merge/9999',
				array(
					'data' => $data,
					'method' => 'post'
				));
		}

		public function testMergeShowForm() {
			$Entries = $this->generate('Entries',
				array(
					'models' => array(
						'Entry' => array('merge')
					)
				));
			$this->_loginUser(2);

			$data = array(
				'Entry' => array()
			);
			$Entries->Entry->expects($this->never())
					->method('merge');
			$result = $this->testAction('/entries/merge/4',
				array(
					'data' => $data,
					'method' => 'post'
				));
			$this->assertFalse(isset($this->headers['Location']));
		}

		public function testMergeIsNotAuthorized() {
			$Entries = $this->generate('Entries',
				array(
					'models' => array(
						'Entry' => array('merge')
					)
				));
			$this->_loginUser(3);

			$data = array(
				'Entry' => array(
					'targetId' => 2,
				)
			);

			$Entries->Entry->expects($this->never())
					->method('merge');
			$this->expectException('MethodNotAllowedException');
			$result = $this->testAction('/entries/merge/4',
				array(
					'data' => $data,
					'method' => 'post'
				));
		}

		public function testMerge() {
			$Entries = $this->generate('Entries',
				array(
					'models' => array(
						'Entry' => array('threadMerge')
					)
				));
			$this->_loginUser(2);

			$data = array(
				'Entry' => array(
					'targetId' => 2,
				)
			);

			$Entries->Entry->expects($this->exactly(1))
					->method('threadMerge')
					->with('2')
					->will($this->returnValue(true));

			$result = $this->testAction('/entries/merge/4',
				array(
					'data' => $data,
					'method' => 'post'
				));
		}

		/**
		 * Entry does not exist
		 */
		public function testEditNoEntry() {
			$Entries = $this->generate('Entries');
			$this->_loginUser(2);
			$this->expectException('NotFoundException');
			$this->testAction('entries/edit/9999');
		}

		/**
		 * Entry does not exist
		 */
		public function testEditNoEntryId() {
			$Entries = $this->generate('Entries');
			$this->_loginUser(2);
			$this->expectException('BadRequestException');
			$this->testAction('entries/edit/');
		}

		/**
		 * Show editing form
		 */
		public function testEditShowForm() {
			$Entries = $this->generate('Entries',
				array(
					'models' => array(
						'Entry' => array(
							'isEditingForbidden',
						)
					)
				));

			$this->_loginUser(2);

			$Entries->Entry->expects($this->any())
					->method('isEditingForbidden')
					->will($this->returnValue(false));

			$result = $this->testAction('entries/edit/2',
				array(
					'return' => 'view'
				));

			// test that subject is quoted
			$this->assertContains('value="Second_Subject"', $result);
			// test that text is quoted
			$this->assertContains('Second_Text</textarea>', $result);
			// notification are un/checked
			$this->assertNoPattern('/data\[Event\]\[1\]\[event_type_id\]"\s+?checked="checked"/', $result);
			$this->assertPattern('/data\[Event\]\[2\]\[event_type_id\]"\s+?checked="checked"/', $result);
		}

		public function testPreviewLoggedIn() {
			$this->setExpectedException('ForbiddenException');
			$this->testAction('/entries/preview');
		}

		public function testPreviewIsAjax() {
			$this->generate('Entries');
			$this->_loginUser(1);
			$this->setExpectedException('BadRequestException');
			$this->testAction('/entries/preview');
		}

		public function testPreviewIsPut() {
			$this->generate('Entries');
			$this->_setAjax();
			$this->_loginUser(1);
			$this->setExpectedException('MethodNotAllowedException');
			$this->testAction('/entries/preview', array('method' => 'GET'));
		}

		/*
		public function testPreview() {
			$this->generate('Entries');
			$this->_setAjax();
			$this->_loginUser(1);

			$this->testAction(
				'/entries/preview',
				array('method' => 'PUT')
			);
		}
		*/

		public function testSearchAdvAccession() {
			$Entries = $this->generate('Entries');
			$this->_loginUser(3);

			$result = $this->testAction('/entries/search/subject:third%20thread/category:/adv:1',
					array('return' => 'vars'));
			$this->assertEmpty($result['FoundEntries']);
		}

		public function testSearchAdvForbiddenCategory() {
			$Entries = $this->generate('Entries');
			$this->_loginUser(3);

			$this->setExpectedException('NotFoundException');
			$this->testAction('/entries/search/subject:test/text:/name:/category:1/month:07/year:2006/adv:1');
		}

		public function testView() {
			//* not logged in user
			$Entries = $this->generate('Entries');
			$this->_logoutUser();

			$result = $this->testAction('/entries/view/1', array('return' => 'vars'));
			$this->assertEqual($result['entry']['Entry']['id'], 1);

			$result = $this->testAction('/entries/view/2');
			$this->assertFalse(isset($this->headers['Location']));

			$result = $this->testAction('/entries/view/4', array('return' => 'view'));
			$this->assertRedirectedTo();

			//* logged in user
			$this->_loginUser(3);
			$result = $this->testAction('/entries/view/4', array('return' => 'vars'));
			$this->assertEqual($result['entry']['Entry']['id'], 4);

			$result = $this->testAction('/entries/view/2', array('return' => 'vars'));
			$this->assertFalse(isset($this->headers['Location']));

			$result = $this->testAction('/entries/view/4', array('return' => 'vars'));
			$this->assertFalse(isset($this->headers['Location']));

			//* redirect to index if entry does not exist
			$result = $this->testAction('/entries/view/9999', array('return' => 'vars'));
			$this->assertRedirectedTo();
		}

		public function testViewBoxFooter() {
			$result = $this->testAction('entries/view/1',
				array(
					'return' => 'view'
				));
			$this->assertTextNotContains('panel-footer panel-form', $result);

			$this->_loginUser(3);
			$result = $this->testAction('entries/view/1',
				array(
					'return' => 'view'
				));
			$this->assertTextContains('panel-footer panel-form', $result);
		}

		/**
		 * Checks that the mod-button is in-/visible
		 */
		public function testViewModButton() {
			/**
			 * Mod Button is not visible for anon users
			 */
			$result = $this->testAction('entries/view/1',
				array(
					'return' => 'view'
				));
			$this->assertTextNotContains('dropdown', $result);

			/**
			 * Mod Button is not visible for normal users
			 */
			$this->_loginUser(3);
			$result = $this->testAction('entries/view/1',
				array(
					'return' => 'view'
				));
			$this->assertTextNotContains('dropdown', $result);

			/**
			 * Mod Button is visible for mods
			 */
			$this->_loginUser(2);
			$result = $this->testAction('entries/view/1',
				array(
					'return' => 'view'
				));
			$this->assertTextContains('dropdown', $result);
		}

		public function testViewSanitation() {
			$result = $this->testAction('/entries/view/11', ['return' => 'view']);
			$this->assertTextNotContains('&<Subject', $result);
			$this->assertTextContains('&amp;&lt;Subject', $result);
			$this->assertTextNotContains('&<Text', $result);
			$this->assertTextContains('&amp;&lt;Text', $result);
			$this->assertTextNotContains('&<Username', $result);
			$this->assertTextContains('&amp;&lt;Username', $result);
		}

		public function testAppStats() {
			Configure::write('Cache.disable', false);
			Cache::delete('header_counter', 'short');

			// test with no user online
			$result = $this->testAction('/entries/index', array('return' => 'vars'));
			$headerCounter = $result['HeaderCounter'];

			$this->assertEqual($headerCounter['user_online'], 1);
			$this->assertEqual($headerCounter['user'], 7);
			$this->assertEqual($headerCounter['entries'], 11);
			$this->assertEqual($headerCounter['threads'], 5);
			$this->assertEqual($headerCounter['user_registered'], 0);
			$this->assertEqual($headerCounter['user_anonymous'], 1);

			// test with one user online
			$this->_loginUser(2);

			$result = $this->testAction('/entries/index', array('return' => 'vars'));
			$headerCounter = $result['HeaderCounter'];

			/* without cache
			$this->assertEqual($headerCounter['user_online'], 2);
			$this->assertEqual($headerCounter['user_registered'], 1);
			$this->assertEqual($headerCounter['user_anonymous'], 1);
			 */

			// with cache
			$this->assertEqual($headerCounter['user_online'], 1);
			$this->assertEqual($headerCounter['user_registered'], 1);
			$this->assertEqual($headerCounter['user_anonymous'], 0);

			// test with second user online
			$this->_loginUser(3);

			$result = $this->testAction('/entries/index', array('return' => 'vars'));
			$headerCounter = $result['HeaderCounter'];

			// with cache
			$this->assertEqual($headerCounter['user_online'], 1);
			$this->assertEqual($headerCounter['user_registered'], 2);
			$this->assertEqual($headerCounter['user_anonymous'], 0);
		}

		public function testFeedJson() {
			$result = $this->testAction('/entries/feed/feed.json', array(
					'return' => 'vars',
			));
			$this->assertEqual($result['entries'][0]['Entry']['subject'], 'First_Subject');
			$this->assertFalse(isset($result['entries'][0]['Entry']['ip']));
		}

		public function testGetViewVarsSearch() {
			/*

			  $this->_loginUser("Charles");
			  $this->_prepareAction('/entries/search', $this->cu['user_id']);

			  $data['Entry']['search']['term'] = 'first_text';
			  $data['Entry']['search']['start']['month'] = '01';
			  $data['Entry']['search']['start']['year'] = '1990';
			  $result = $this->testAction('/entries/search', array( 'return' => 'vars', 'data' => $data));
			  $this->assertEqual($this->cu['user_id'], $result['FoundEntries']['0']['Entry']['user_id']);
			  $this->assertEqual($this->cu['user_id'], $result['FoundEntries']['0']['User']['user_id']);
			  $this->assertEqual('First_Text', $result['FoundEntries']['0']['Entry']['text']);

			  $data['Entry']['search']['term'] = 'first_subject';
			  $data['Entry']['search']['start']['month'] = '01';
			  $data['Entry']['search']['start']['year'] = '1990';
			  $result = $this->testAction('/entries/search', array( 'return' => 'vars', 'data' => $data));
			  $this->assertEqual($this->cu['user_id'], $result['FoundEntries']['0']['Entry']['user_id']);
			  $this->assertEqual($this->cu['user_id'], $result['FoundEntries']['0']['User']['user_id']);
			  $this->assertEqual('First_Text', $result['FoundEntries']['0']['Entry']['text']);
			 */
		}

		public function testSearchStringSanitizer() {
			$data = 'foo bar +baz -zoo \'';
			$expected = '+foo +bar +baz -zoo +\\\'';

			$Entries = $this->generate('EntriesMock');
			$result = $Entries->searchStringSanitizer($data);
			$this->assertEquals($expected, $result);
		}

		public function testSolveNotLoggedIn() {
			$this->generate('Entries', ['methods' => 'solve']);
			$this->testAction('/entries/solve/1');
			$this->assertRedirectedTo('login');
		}

		public function testSolveNoEntry() {
			$this->generate('Entries');
			$this->_loginUser(1);
			$this->expectException('BadRequestException');
			$this->testAction('/entries/solve/9999');
		}

		public function testSolveNotRootEntryUser() {
			$this->generate('Entries');
			$this->_loginUser(2);
			$this->expectException('ForbiddenException');
			$this->testAction('/entries/solve/1');
		}

		public function testSolveIsRootEntry() {
			$this->generate('Entries');
			$this->_loginUser(3);
			$this->expectException('BadRequestException');
			$this->testAction('/entries/solve/1');
		}

		public function testSolveSaveError() {
			$Entries = $this->generate('Entries', ['models' => ['Entry' => ['toggleSolve']]]);
			$this->_loginUser(3);
			$Entries->Entry->expects($this->once())
				->method('toggleSolve')
				->with('1')
				->will($this->returnValue(false));
			$this->expectException('BadRequestException');
			$this->testAction('/entries/solve/1');
		}

		public function testSolve() {
			$Entries = $this->generate('Entries', ['models' => ['Entry' => ['toggleSolve']]]);
			$this->_loginUser(3);
			$Entries->Entry->expects($this->once())
					->method('toggleSolve')
					->with('1')
					->will($this->returnValue(true));
			$result = $this->testAction('/entries/solve/1');
			$this->assertTextEquals($result, '');
		}

		//-----------------------------------------------

		public function setUp() {
			parent::setUp();
			$this->_saitoSettingsTopicsPerPage = Configure::read('Saito.Settings.topics_per_page');
			Configure::write('Saito.Settings.topics_per_page', 20);
		}

		public function tearDown() {
			parent::tearDown();
			Configure::write('Saito.Settings.topics_per_page',
				$this->_saitoSettingsTopicsPerPage);
		}

	}
