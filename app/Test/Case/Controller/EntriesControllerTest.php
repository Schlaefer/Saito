<?php

	App::uses('Controller', 'Controller');
	App::uses('EntriesController', 'Controller');
	App::uses('SaitoControllerTestCase', 'Lib');

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
		);

		public function testIndex() {

			//* not logged in user
			$result = $this->testAction('/entries/index', array( 'return' => 'vars' ));
			$entries = $result['entries'];
			$this->assertEqual(count($entries), 1);

			//* logged in user
			$this->_loginUser(3);
			$result = $this->testAction('/entries/index', array( 'return' => 'vars' ));
			$entries = $result['entries'];
			$this->assertEqual(count($entries), 2);
		}

		public function testView() {
			//* not logged in user
			$result = $this->testAction('/entries/view/1', array( 'return' => 'vars' ));
			$this->assertEqual($result['entry']['Entry']['id'], 1);

			$result = $this->testAction('/entries/view/2');
			$this->assertFalse(isset($this->headers['Location']));

			$result = $this->testAction('/entries/view/4');
			$this->assertEqual(FULL_BASE_URL . $this->controller->request->webroot, $this->headers['Location']);

			//* logged in user
			$this->_loginUser(3);
			$result = $this->testAction('/entries/view/4', array( 'return' => 'vars' ));
			$this->assertEqual($result['entry']['Entry']['id'], 4);

			$result = $this->testAction('/entries/view/2', array( 'return' => 'vars' ));
			$this->assertFalse(isset($this->headers['Location']));

			$result = $this->testAction('/entries/view/4', array( 'return' => 'vars' ));
			$this->assertFalse(isset($this->headers['Location']));

			//* redirect to index if entry does not exist
			$result = $this->testAction('/entries/view/9999', array( 'return' => 'vars' ));
			$this->assertEqual(FULL_BASE_URL . $this->controller->request->webroot, $this->headers['Location']);
		}

		public function testHeaderCounter() {

			$this->_prepareAction('/entries/index');

			//* test with no user online
			$result = $this->testAction('/entries/index', array( 'return' => 'vars' ));
			$headerCounter = $result['HeaderCounter'];

			$this->assertEqual($headerCounter['user_online'], 1);
			$this->assertEqual($headerCounter['user'], 5);
			$this->assertEqual($headerCounter['entries'], 4);
			$this->assertEqual($headerCounter['threads'], 2);
			$this->assertEqual($headerCounter['user_registered'], 0);
			$this->assertEqual($headerCounter['user_anonymous'], 1);


			//* test with one user online
			$this->_loginUser(2);

			$result = $this->testAction('/entries/index', array( 'return' => 'vars' ));
			$headerCounter = $result['HeaderCounter'];

			$this->assertEqual($headerCounter['user_online'], 2);
			$this->assertEqual($headerCounter['user_registered'], 1);
			$this->assertEqual($headerCounter['user_anonymous'], 1);
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