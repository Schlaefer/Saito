<?php

	App::uses('Controller', 'Controller');
	App::uses('EntriesController', 'Controller');
	App::uses('SaitoControllerTestCase', 'Lib/Test');

	class EntriesMockController extends EntriesController {

		// @codingStandardsIgnoreStart
		public $uses = array('Entry');
		// @codingStandardsIgnoreEnd

		public function getInitialThreads($User, $order = ['Entry.last_answer' => 'DESC']) {
			$this->_getInitialThreads($User, $order);
		}

	}

	class EntriesControllerTestCase extends \Saito\Test\ControllerTestCase {

		use \Saito\Test\SecurityMockTrait;

		public $fixtures = [
			'app.bookmark',
			'app.category',
			'app.entry',
			'app.esevent',
			'app.esnotification',
			'app.setting',
			'app.shout',
			'app.smiley',
			'app.smiley_code',
			'app.upload',
			'app.user',
			'app.user_block',
			'app.user_ignore',
			'app.user_online',
			'app.user_read'
		];

		public function testMix() {
			$result = $this->testAction('/entries/mix/1', array('return' => 'vars'));
			$this->assertStringStartsWith('First_Subject',
				$result['title_for_layout']);
		}

		public function testMixNoAuthorization() {
			$this->testAction('/entries/mix/4', ['return' => 'view']);
			$this->assertEquals($this->controller->Auth->redirectUrl(), '/entries/mix/4');
			$this->assertRedirectedTo('login');
		}

		public function testMixNotFound() {
			$this->generate('Entries', array());
			$this->setExpectedException('NotFoundException');
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
					'category_id' => 1,
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

		public function testAddSubjectToLong() {
			$Entries = $this->generate( 'Entries');
			$this->_loginUser(1);

			// maxlength attribute is set for textfield
			$result = $this->testAction('entries/add',
				['method' => 'GET', 'return' => 'view']);
			$this->assertTextContains('maxlength="40"', $result);

			// subject is one char to long
			$data = [
					'Entry' => [
						// 41 chars
							'subject' => 'Vorher wie ich in der mobilen Version ka…',
							'category_id' => 1,
							'pid' => 0
					],
					'Event' => [
							1 => ['event_type_id' => 0],
							2 => ['event_type_id' => 1]
					]
			];

			$result = $this->testAction(
					'entries/add',
					['data' => $data, 'method' => 'POST', 'return' => 'view']
			);
			$this->assertTextContains('Subject is to long.', $result);
			$id = $Entries->Entry->find('count') + 1;

			// subject has max length
			$data['Entry']['subject'] = mb_substr($data['Entry']['subject'], 1);
			$this->testAction(
					'entries/add',
					['data' => $data, 'method' => 'POST']
			);

			$this->assertRedirectedTo('entries/view/' . $id);
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
			$Entries = $this->generate('EntriesMock', ['methods' => ['paginate']]);

			Configure::write('Saito.Settings.category_chooser_global', 1);

			$Entries->expects($this->once())
					->method('paginate')
					->will($this->returnValue(array()));

			App::uses('CurrentUserComponent', 'Controller/Component');
			App::uses('ComponentCollection', 'Controller');
			$User = new CurrentUserComponent(new ComponentCollection());

			$User->Categories = $this->getMock('Saito\User\Auth\CategoryAuthorization', ['getAllowed'],
				[$User]);
			$User->Categories->expects($this->any())
				->method('getAllowed')
				->will($this->returnValue([
						1 => 'Admin',
						2 => 'Ontopic',
						7 => 'Foo'
					]
				));

			$User->setSettings([]);
			$Entries->getInitialThreads($User);
			$this->assertFalse(isset($Entries->viewVars['categoryChooser']));
		}

		/**
		 * Admin completely deactivated category-chooser
		 */
		public function testCategoryChooserDeactivated() {
			$Entries = $this->generate('EntriesMock', ['methods' => ['paginate']]);

			Configure::write('Saito.Settings.category_chooser_global', 0);
			Configure::write('Saito.Settings.category_chooser_user_override', 0);

			$Entries->expects($this->once())
					->method('paginate')
					->will($this->returnValue(array()));

			App::uses('CurrentUserComponent', 'Controller/Component');
			App::uses('ComponentCollection', 'Controller');
			$User = new CurrentUserComponent(new ComponentCollection());

			$User->Categories = $this->getMock(
				'Saito\User\Auth\CategoryAuthorization',
				['getAllowed'],
				[$User]
			);
			$User->Categories->expects($this->any())
				->method('getAllowed')
				->will($this->returnValue([
						1 => 'Admin',
						2 => 'Ontopic',
						7 => 'Foo'
					]
				));

			$User->setSettings(array(
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
			$Entries = $this->generate('EntriesMock', ['methods' => ['paginate']]);

			Configure::write('Saito.Settings.category_chooser_global', 0);
			Configure::write('Saito.Settings.category_chooser_user_override', 1);

			$Entries->expects($this->once())
					->method('paginate')
					->will($this->returnValue(array()));

			App::uses('CurrentUserComponent', 'Controller/Component');
			App::uses('ComponentCollection', 'Controller');
			$User = new CurrentUserComponent(new ComponentCollection());

			$User->Categories = $this->getMock(
				'Saito\User\Auth\CategoryAuthorization',
				['getAllowed'],
				[$User]
			);
			$User->Categories->expects($this->any())
				->method('getAllowed')
				->will($this->returnValue([
						1 => 'Admin',
						2 => 'Ontopic',
						7 => 'Foo'
					]
				));

			$User->setSettings(array(
				'id' => 1,
				'user_sort_last_answer' => 1,
				'user_type' => 'admin',
				'user_category_active' => 0,
				'user_category_custom' => array(),
				'user_category_override' => 1,
			));
			$Entries->getInitialThreads($User);
			$this->assertTrue(isset($Entries->viewVars['categoryChooser']));
			$this->assertEquals($Entries->viewVars['categoryChooserTitleId'], 'All Categories');
		}

		/**
		 * Test custom set
		 *
		 * - new categories (8) are in the custom set
		 */
		public function testCategoryChooserCustomSet() {
			$Entries = $this->generate('EntriesMock', ['methods' => ['paginate']]);

			Configure::write('Saito.Settings.category_chooser_global', 1);

			$Entries->expects($this->once())
					->method('paginate')
					->will($this->returnValue(array()));

			App::uses('CurrentUserComponent', 'Controller/Component');
			App::uses('ComponentCollection', 'Controller');
			$User = new CurrentUserComponent(new ComponentCollection());

			$User->Categories = $this->getMock(
				'Saito\User\Auth\CategoryAuthorization',
				['getAllowed'],
				[$User]
			);
			$User->Categories->expects($this->any())
				->method('getAllowed')
				->will($this->returnValue([
						2 => 'Ontopic',
						7 => 'Foo',
						8 => 'Bar'
					]
				));

			$User->setSettings(array(
				'id' => 1,
				'user_sort_last_answer' => 1,
				'user_type' => 'admin',
				'user_category_active' => 0,
				'user_category_custom' => array(1 => 1, 2 => 1, 7 => 0),
			));
			$Entries->getInitialThreads($User);
			$this->assertTrue(isset($Entries->viewVars['categoryChooser']));
			$this->assertEquals($Entries->viewVars['categoryChooserChecked'],
				array(
					'2' => '2',
					'8' => '8',
				));
			$this->assertEquals($Entries->viewVars['categoryChooser'],
				array(
					'2' => 'Ontopic',
					'7' => 'Foo',
					'8' => 'Bar',
				));
			$this->assertEquals($Entries->viewVars['categoryChooserTitleId'],
				'Custom');
		}

		public function testCategoryChooserSingleCategory() {
			$Entries = $this->generate('EntriesMock', ['methods' => ['paginate']]);

			Configure::write('Saito.Settings.category_chooser_global', 1);

			$Entries->expects($this->once())
					->method('paginate')
					->will($this->returnValue(array()));

			App::uses('CurrentUserComponent', 'Controller/Component');
			App::uses('ComponentCollection', 'Controller');
			$User = new CurrentUserComponent(new ComponentCollection());

			$User->Categories = $this->getMock(
				'Saito\User\Auth\CategoryAuthorization',
				['getAllowed'],
				[$User]
			);
			$User->Categories->expects($this->any())
				->method('getAllowed')
				->will($this->returnValue([
						1 => 'Admin',
						2 => 'Ontopic',
						7 => 'Foo'
					]
				));

			$User->setSettings(array(
				'id' => 1,
				'user_sort_last_answer' => 1,
				'user_type' => 'admin',
				'user_category_active' => 7,
				'user_category_custom' => array(1 => 1, 2 => 1, 7 => 0),
			));
			$Entries->getInitialThreads($User);
			$this->assertTrue(isset($Entries->viewVars['categoryChooser']));
			$this->assertEquals($Entries->viewVars['categoryChooserTitleId'], 7);
			$this->assertEquals($Entries->viewVars['categoryChooserChecked'],
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
			$this->assertEquals(count($entries), 3);

			//* logged in user
			$this->_loginUser(3);
			$result = $this->testAction('/entries/index', array('return' => 'vars'));
			$entries = $result['entries'];
			$this->assertEquals(count($entries), 4);
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
			$this->setExpectedException('NotFoundException');
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
			$this->setExpectedException('NotFoundException');
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
			$this->setExpectedException('MethodNotAllowedException');
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
			$this->setExpectedException('NotFoundException');
			$this->testAction('entries/edit/9999');
		}

		/**
		 * Entry does not exist
		 */
		public function testEditNoEntryId() {
			$Entries = $this->generate('Entries');
			$this->_loginUser(2);
			$this->setExpectedException('BadRequestException');
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

			$this->_loginUser(1);

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
			$this->assertNotRegExp('/data\[Event\]\[1\]\[event_type_id\]"\s+?checked="checked"/', $result);
			$this->assertRegExp('/data\[Event\]\[2\]\[event_type_id\]"\s+?checked="checked"/', $result);
		}

		/**
		 * tests that the form renders without error if saving fails
		 *
		 * doesn't test for any specific validation error
		 */
		public function testEditNoInternalErrorOnValidationError() {
			$Entries = $this->generate('Entries', [
				'models' => ['Entry' => ['get', 'update']]
			]);

			$Entries->Entry->expects($this->at(0))
				->method('get')
				->with(2)
				->will($this->returnValue([
					'Entry' => [
						'id' => 2,
						'tid' => 1,
						'pid' => 1,
						'time' => time() - 1,
						'user_id' => 2,
						'fixed' => false
					],
					'User' => [
						'username' => 'Mitch'
					],
					'rights' => [
						'isEditingAsUserForbidden' => false,
						'isEditingForbidden' => false
					]
				]));
			$Entries->Entry->expects($this->once())
				->method('update')
				->will($this->returnValue(false));

			$this->_loginUser(1);
			$this->testAction('entries/edit/2', [
				'data' => [
					'Entry' => [
						'pid' => 1,
					]
				],
				'method' => 'POST'
			]);
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

		public function testView() {
			//# not logged in user
			$Entries = $this->generate('Entries');
			$this->_logoutUser();

			$result = $this->testAction('/entries/view/1', array('return' => 'vars'));
			$this->assertEquals($result['entry']['Entry']['id'], 1);

			$this->testAction('/entries/view/2');
			$this->assertFalse(isset($this->headers['Location']));

			$this->testAction('/entries/view/4', array('return' => 'view'));
			$this->assertEquals($Entries->Auth->redirectUrl(), '/entries/view/4');
			$this->assertRedirectedTo('login');

			//# logged in user
			$this->_loginUser(3);
			$result = $this->testAction('/entries/view/4', array('return' => 'vars'));
			$this->assertEquals($result['entry']['Entry']['id'], 4);

			$this->testAction('/entries/view/2', array('return' => 'vars'));
			$this->assertFalse(isset($this->headers['Location']));

			$this->testAction('/entries/view/4', array('return' => 'vars'));
			$this->assertFalse(isset($this->headers['Location']));

			//* redirect to index if entry does not exist
			$this->testAction('/entries/view/9999', array('return' => 'vars'));
			$this->assertRedirectedTo();
		}

		public function testViewIncreaseViewCounterNotLoggedIn() {
			$Entries = $this->generate('Entries', [
				'models' => ['Entry' => ['incrementViews']]
			]);
			$id = 1;
			$Entries->Entry->expects($this->once())
				->method('incrementViews')
				->with($id);
			$this->testAction('/entries/view/' . $id);
		}

		public function testViewIncreaseViewCounterLoggedIn() {
			$Entries = $this->generate('Entries', [
				'models' => ['Entry' => ['incrementViews']]
			]);
			$id = 1;
			$Entries->Entry->expects($this->once())
				->method('incrementViews')
				->with($id);
			$this->_loginUser(1);
			$this->testAction('/entries/view/' . $id);
		}

		/**
		 * don't increase view counter if user views its own posting
		 */
		public function testViewIncreaseViewCounterSameUser() {
			$Entries = $this->generate('Entries', [
				'models' => ['Entry' => ['incrementViews']]
			]);
			$id = 1;
			$this->_loginUser(3);

			$Entries->Entry->expects($this->never())
				->method('incrementViews');
			$this->testAction('/entries/view/' . $id);
		}

		/**
		 * don't increase view counter on spiders/crawlers
		 */
		public function testViewIncreaseViewCounterCrawler() {
			$this->_setUserAgent('A Crawler Agent');
			$id = 1;
			$Entries = $this->generate('Entries', [
				'models' => ['Entry' => ['incrementViews']]
			]);

			$Entries->Entry->expects($this->never())
				->method('incrementViews');

			$this->testAction('/entries/view/' . $id);
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

		public function testThreadLineAnon() {
			$Entries = $this->generate(
				'Entries',
				['components' => ['Auth' => ['_stop']]]
			);
			$Entries->Auth->expects($this->once())->method('_stop');
			$result = $this->testAction('/entries/threadLine/1.json');
			// $this->assertRedirectedTo('login');
		}

		public function testThreadLineForbidden() {
			$this->generate('Entries');
			$this->_loginUser(3);
			$this->testAction('/entries/threadLine/6.json');
			$this->assertRedirectedTo('login');
		}

		public function testThreadLineSucces() {
			$this->generate('Entries');
			$this->_loginUser(1);
			$result = $this->testAction('/entries/threadLine/6.json', ['return' => 'view']);
			$expected = 'Third Thread First_Subject';
			$this->assertContains($expected, $result);
		}

		public function testAppStats() {
			Configure::write('Cache.disable', false);
			Cache::delete('header_counter', 'short');

			// test with no user online
			$result = $this->testAction('/entries/index',
				['method' => 'GET', 'return' => 'vars']);
			$headerCounter = $result['HeaderCounter'];

			$this->assertEquals($headerCounter['user_online'], 1);
			$this->assertEquals($headerCounter['user'], 10);
			$this->assertEquals($headerCounter['entries'], 11);
			$this->assertEquals($headerCounter['threads'], 5);
			$this->assertEquals($headerCounter['user_registered'], 0);
			$this->assertEquals($headerCounter['user_anonymous'], 1);

			// test with one user online
			$this->_loginUser(2);

			$result = $this->testAction('/entries/index',
				['method' => 'GET', 'return' => 'vars']);
			$headerCounter = $result['HeaderCounter'];

			/* without cache
			$this->assertEquals($headerCounter['user_online'], 2);
			$this->assertEquals($headerCounter['user_registered'], 1);
			$this->assertEquals($headerCounter['user_anonymous'], 1);
			 */

			// with cache
			$this->assertEquals($headerCounter['user_online'], 1);
			$this->assertEquals($headerCounter['user_registered'], 1);
			$this->assertEquals($headerCounter['user_anonymous'], 0);

			// test with second user online
			$this->_loginUser(3);

			$result = $this->testAction('/entries/index',
				['method' => 'GET', 'return' => 'vars']);
			$headerCounter = $result['HeaderCounter'];

			// with cache
			$this->assertEquals($headerCounter['user_online'], 1);
			$this->assertEquals($headerCounter['user_registered'], 2);
			$this->assertEquals($headerCounter['user_anonymous'], 0);
		}

		public function testFeedJson() {
			$result = $this->testAction('/entries/feed/feed.json',
				['method' => 'GET', 'return' => 'vars']);
			$this->assertEquals($result['entries'][0]['Entry']['subject'], 'First_Subject');
			$this->assertFalse(isset($result['entries'][0]['Entry']['ip']));
		}

		public function testSolveNotLoggedIn() {
			$this->generate('Entries', ['methods' => 'solve']);
			$this->testAction('/entries/solve/1');
			$this->assertRedirectedTo('login');
		}

		public function testSolveNoEntry() {
			$this->generate('Entries');
			$this->_loginUser(1);
			$this->setExpectedException('BadRequestException');
			$this->testAction('/entries/solve/9999');
		}

		public function testSolveNotRootEntryUser() {
			$this->generate('Entries');
			$this->_loginUser(2);
			$this->setExpectedException('BadRequestException');
			$this->testAction('/entries/solve/1');
		}

		public function testSolveIsRootEntry() {
			$this->generate('Entries');
			$this->_loginUser(3);
			$this->setExpectedException('BadRequestException');
			$this->testAction('/entries/solve/1');
		}

		public function testSolveSaveError() {
			$Entries = $this->generate('Entries', ['models' => ['Entry' => ['toggleSolve']]]);
			$this->_loginUser(3);
			$Entries->Entry->expects($this->once())
				->method('toggleSolve')
				->with('1')
				->will($this->returnValue(false));
			$this->setExpectedException('BadRequestException');
			$this->testAction('/entries/solve/1');
		}

		public function testSeo() {
			$config = Configure::read('App');
			$config['baseUrl'] = '/';
			Configure::write('App', $config);

			$result = $this->testAction('/entries/index',
				['method' => 'GET', 'return' => 'contents']);
			$this->assertTextNotContains('noindex', $result);
			$expected = '<link rel="canonical" href="' . Router::url('/',
							true) . '"/>';
			$this->assertTextContains($expected, $result);

			Configure::write('Saito.Settings.topics_per_page', 2);
			$result = $this->testAction('/entries/index/page:2/',
				['method' => 'GET', 'return' => 'contents']);
			$this->assertTextNotContains('rel="canonical"', $result);
			$expected = '<meta name="robots" content="noindex, follow">';
			$this->assertTextContains($expected, $result);
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
