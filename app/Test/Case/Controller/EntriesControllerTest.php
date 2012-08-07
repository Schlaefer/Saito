<?php

	App::uses('Controller', 'Controller');
	App::uses('EntriesController', 'Controller');
	App::uses('SaitoControllerTestCase', 'Lib');

	class EntriesMockController extends EntriesController {
		public $uses = array('Entry');

		public function getInitialThreads($User) {
			$this->_getInitialThreads($User);
		}
	}

	class EntriesControllerTestCase extends SaitoControllerTestCase {

		public $fixtures = array(
				'app.user',
				'app.user_online',
				'app.entry',
				'app.category',
				'app.smiley',
				'app.smiley_code',
				'app.setting',
				'app.upload',
				'app.esnotification',
				'app.esevent',
		);

		public function testBookmarkButtonVisibility() {

			$result = $this->testAction('/entries/view/1', array('return' => 'view'));
			$this->assertNotContains('bookmarks-subscribe-1', $result);

			$this->_loginUser(3);
			$result = $this->testAction('/entries/view/1', array('return' => 'view'));
			$this->assertContains('bookmarks-subscribe-1', $result);

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
									1 => '1', 2 => '2', 7 => '7'
									)
							));

			App::uses('CurrentUserComponent', 'Controller/Component');
			App::uses('ComponentCollection', 'Controller');
			$User = new CurrentUserComponent(new ComponentCollection());
			$User->set(array());
			$Entries->getInitialThreads($User);
			$this->assertFalse($Entries->viewVars['categoryChooserIsUsed']);
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
									1 => '1', 2 => '2', 7 => '7'
									)
							));

			App::uses('CurrentUserComponent', 'Controller/Component');
			App::uses('ComponentCollection', 'Controller');
			$User = new CurrentUserComponent(new ComponentCollection());
			$User->set(array(
					'id'										 => 1,
					'user_sort_last_answer'	 => 1,
					'user_type'							 => 'admin',
					'user_category_active'	 => 0,
					'user_category_custom'	 => '',
					'user_category_override' => 1,
			));
			$Entries->getInitialThreads($User);
			$this->assertFalse($Entries->viewVars['categoryChooserIsUsed']);
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
									1 => '1', 2 => '2', 7 => '7'
									)
							));

			App::uses('CurrentUserComponent', 'Controller/Component');
			App::uses('ComponentCollection', 'Controller');
			$User = new CurrentUserComponent(new ComponentCollection());
			$User->set(array());
			$User->set(array(
					'id'										 => 1,
					'user_sort_last_answer'	 => 1,
					'user_type'							 => 'admin',
					'user_category_active'	 => 0,
					'user_category_custom'	 => array(),
					'user_category_override' => 1,
			));
			$Entries->getInitialThreads($User);
			$this->assertTrue($Entries->viewVars['categoryChooserIsUsed']);
			$this->assertEqual($Entries->viewVars['categoryChooserTitleId'], 'All');
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
							'Category' => array('getCategoriesForAccession', 'getCategoriesSelectForAccession'),
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
									2 => '2', 7 => '7', 8 => '8'
									)
							));
			$Entries->Entry->Category->expects($this->once())
					->method('getCategoriesSelectForAccession')
					->will($this->returnValue(array(
									2 => 'Ontopic', 7 => 'Foo', 8 => 'Bar'
									)
							));

			App::uses('CurrentUserComponent', 'Controller/Component');
			App::uses('ComponentCollection', 'Controller');
			$User = new CurrentUserComponent(new ComponentCollection());
			$User->set(array());
			$User->set(array(
					'id'										 => 1,
					'user_sort_last_answer'	 => 1,
					'user_type'							 => 'admin',
					'user_category_active'	 => 0,
					'user_category_custom'	 => array(1 => 1, 2 => 1, 7 => 0),
			));
			$Entries->getInitialThreads($User);
			$this->assertTrue($Entries->viewVars['categoryChooserIsUsed']);
			$this->assertEqual($Entries->viewVars['categoryChooserChecked'], array(
					'2' => 1,
					'8' => '8',
					));
			$this->assertEqual($Entries->viewVars['categoryChooser'], array(
					'2' => 'Ontopic',
					'7' => 'Foo',
					'8' => 'Bar',
					));
			$this->assertEqual($Entries->viewVars['categoryChooserTitleId'], 'Custom');
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
									1 => '1', 2 => '2', 7 => '7'
									)
							));

			App::uses('CurrentUserComponent', 'Controller/Component');
			App::uses('ComponentCollection', 'Controller');
			$User = new CurrentUserComponent(new ComponentCollection());
			$User->set(array());
			$User->set(array(
					'id'										 => 1,
					'user_sort_last_answer'	 => 1,
					'user_type'							 => 'admin',
					'user_category_active'	 => 7,
					'user_category_custom'	 => array(1 => 1, 2 => 1, 7 => 0),
			));
			$Entries->getInitialThreads($User);
			$this->assertTrue($Entries->viewVars['categoryChooserIsUsed']);
			$this->assertEqual($Entries->viewVars['categoryChooserTitleId'], 7);
			$this->assertEqual($Entries->viewVars['categoryChooserChecked'], array(
					'1' => 1,
					'2' => 1,
					));
		}

		public function testIndex() {

			$Entries = $this->generate('Entries');
			$this->_logoutUser();

			//* not logged in user
			$result = $this->testAction('/entries/index', array('return' => 'vars'));
			$entries = $result['entries'];
			$this->assertEqual(count($entries), 1);

			//* logged in user
			$this->_loginUser(3);
			$result = $this->testAction('/entries/index', array('return' => 'vars'));
			$entries = $result['entries'];
			$this->assertEqual(count($entries), 2);
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
					'data'	 => $data, 'method' => 'post'
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
					'data'	 => $data, 'method' => 'post'
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
					'data'	 => $data, 'method' => 'post'
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
					'data'	 => $data, 'method' => 'post'
					));
		}

		public function testMerge() {

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

			$Entries->Entry->expects($this->exactly(1))
					->method('merge')
					->with('2')
					->will($this->returnValue(true));

			$result = $this->testAction('/entries/merge/4',
					array(
					'data'	 => $data, 'method' => 'post'
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
			$this->expectException('NotFoundException');
			$this->testAction('entries/edit/');
		}

		/**
		 * Show editing form
		 */
		public function testEditShowForm() {
			$Entries = $this->generate('Entries', array(
				'components' => array('SaitoEntry')
			));
//			$Entries->SaitoEntry = $this->getMock('SaitoEntry', null, array(new ComponentCollection));
			$Entries->SaitoEntry->expects($this->once())
					->method('isEditingForbidden')
					->will($this->returnValue(false));

			$this->_loginUser(2);

			$result = $this->testAction('entries/edit/2', array(
					'return' => 'view'
			));

			// test that subject is quoted
			$this->assertContains('value="Second_Subject"', $result);
			// test that text is quoted
			$this->assertContains('Second_Text</textarea>', $result);
			// notification are un/checked
			$this->assertNotRegEx('/data\[Event\]\[1\]\[event_type_id\]"\s+?checked="checked"/', $result);
			$this->assertRegEx('/data\[Event\]\[2\]\[event_type_id\]"\s+?checked="checked"/', $result);
		}

    public function testEmptyCache() {

      $Entries = $this->generate('Entries', array(
          'components' => array(
            'CacheTree' => array('delete'),
          )
      ));

      /*
       * setup
       */
      $this->_loginUser(1);

      $data['Entry'] = array(
          'pid' => 5,
          'subject' => 'test',
          'category'  => 4,
      );

      /*
       * test entries/add
       */
      $Entries->CacheTree
          ->expects($this->once())
          ->method('delete')
          ->with($this->equalTo('4'));

			$result = $this->testAction('/entries/add/5', array(
          'data' => $data,
          'method' => 'post'));

      /*
       * Test entries/edit
       */
      $Entries = $this->generate('Entries', array(
          'components' => array(
            'CacheTree' => array('delete'),
          )
      ));

      $Entries->CacheTree
          ->expects($this->once())
          ->method('delete')
          ->with($this->equalTo('4'));
			$result = $this->testAction('/entries/edit/5', array(
          'data' => $data,
          'method' => 'post'));
    }

		public function testSetcategoryNotLoggedIn() {
				$Entries = $this->generate('Entries', array(
					'models' => array(
							'User' => array('set', 'save')
							)
					));
				$this->_logoutUser();

				$this->setExpectedException('MethodNotAllowedException');
				$this->testAction('/entries/setcategory/all');
		}

		public function testSetcategoryAllGet() {
				$Entries = $this->generate('Entries', array(
					'models' => array(
							'User' => array('set', 'save')
							)
					));

				$this->_loginUser(3);

				$Entries->User->expects($this->once())
						->method('set')
						->with('user_category_active', -1);
				$Entries->User->expects($this->once())
						->method('save');

				$this->testAction('/entries/setcategory/all');
		}

		public function testSetcategoryAllPost() {
				$Entries = $this->generate('Entries', array(
					'models' => array(
							'User' => array('set', 'save')
							)
					));

				$this->_loginUser(3);

				$data = array(
						'CatChooser' => array(
								'4' => '0',
								'7' => '1',
								'9' => '0',
						),
						'CatMeta' => array(
								'All' => '1',
						)
				);

				$Entries->User->expects($this->once())
						->method('set')
						->with('user_category_active', -1);
				$Entries->User->expects($this->once())
						->method('save');

				$this->testAction('/entries/setcategory/all');

		}

		public function testSetcategoryCategory() {
				$Entries = $this->generate('Entries', array(
					'models' => array(
							'User' => array('set', 'save')
							)
					));

				$this->_loginUser(3);

				$Entries->User->expects($this->once())
						->method('set')
						->with('user_category_active', 5);
				$Entries->User->expects($this->once())
						->method('save');

				$this->testAction('/entries/setcategory/5');
		}

		public function testSetcategoryCategories() {
				$Entries = $this->generate('Entries', array(
					'models' => array(
							'User' => array('set', 'save')
							)
					));

				$this->_loginUser(3);

				$data = array(
						'CatChooser' => array(
								'4' => '0',
								'7' => '1',
								'9' => '0',
						),
						'CatMeta' => array(
								'All' => '0',
						)
				);

				$dataAt2 = $data['CatChooser'];

				$Entries->User->expects($this->at(0))
						->method('set')
						->with('user_category_active', 0);
				$Entries->User->expects($this->at(1))
						->method('set')
						->with('user_category_custom', $dataAt2);
				$Entries->User->expects($this->once())
						->method('save');

				$this->testAction('/entries/setcategory/', array(
						'data' => $data, 'method' => 'post'
				));
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
			$this->assertEqual(FULL_BASE_URL . $this->controller->request->webroot,
					$this->headers['Location']);

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
			$this->assertEqual(FULL_BASE_URL . $this->controller->request->webroot,
					$this->headers['Location']);
		}

		public function testViewBoxFooter() {
			$result = $this->testAction('entries/view/1', array(
					'return' => 'view'
			));
			$this->assertTextNotContains('box-footer-entry-actions-1', $result);

			$this->_loginUser(3);
			$result = $this->testAction('entries/view/1', array(
					'return' => 'view'
			));
			$this->assertTextContains('box-footer-entry-actions-1', $result);

		}

		/**
		 * Checks that the mod-button is in-/visible
		 */
		public function testViewModButton() {
			/**
			 * Mod Button is not visible for anon users
			 */
			$result = $this->testAction('entries/view/1', array(
					'return' => 'view'
			));
			$this->assertTextNotContains('button_mod_panel', $result);

			/**
			 * Mod Button is not visible for normal users
			 */
			$this->_loginUser(3);
			$result = $this->testAction('entries/view/1', array(
					'return' => 'view'
			));
			$this->assertTextNotContains('button_mod_panel', $result);

			/**
			 * Mod Button is visible for mods
			 */
			$this->_loginUser(2);
			$result = $this->testAction('entries/view/1', array(
					'return' => 'view'
			));
			$this->assertTextContains('button_mod_panel', $result);

			/**
			 * Currently mod menu is empty if mod is on his own posting
			 */
			$this->_loginUser(2);
			$result = $this->testAction('entries/view/2', array(
					'return' => 'view'
			));
			$this->assertTextNotContains('button_mod_panel', $result);
		}

		public function testAppStats() {

			Configure::write('Cache.disable', false);
			Cache::delete('header_counter', 'perf-cheat');

			// test with no user online
			$result = $this->testAction('/entries/index', array('return' => 'vars'));
			$headerCounter = $result['HeaderCounter'];

			$this->assertEqual($headerCounter['user_online'], 1);
			$this->assertEqual($headerCounter['user'], 6);
			$this->assertEqual($headerCounter['entries'], 5);
			$this->assertEqual($headerCounter['threads'], 2);
			$this->assertEqual($headerCounter['user_registered'], 0);
			$this->assertEqual($headerCounter['user_anonymous'], 1);

			// test with one user online
			$this->_loginUser(2);

			$result = $this->testAction('/entries/index', array('return'			 => 'vars'));
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

			$result = $this->testAction('/entries/index', array('return'			 => 'vars'));
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

		//-----------------------------------------------

		public function setUp() {
			parent::setUp();
			$this->_saito_settings_topics_per_page = Configure::read('Saito.Settings.topics_per_page');
			Configure::write('Saito.Settings.topics_per_page', 20);
		}

		public function tearDown() {
			parent::tearDown();
			Configure::write('Saito.Settings.topics_per_page',
					$this->_saito_settings_topics_per_page);
		}

	}

?>