<?php
/* Entries Test cases generated on: 2010-07-08 18:07:45 : 1278607425*/
App::import('Controller', 'Entries');

require_once '_saito_controller_test_case.php';
//App::import('Component', 'Sessions');

class TestEntriesController extends EntriesController {
	var $name = 'Entries';
//	var $autoRender = false;

	public $redirectUrl = null;
	public $renderedAction = null;

	function render($action = null, $layout = null, $file = null) {
		$this->renderedAction = $action;
		}

	function redirect($url, $status = null, $exit = true) {
		$this->redirectUrl = $url;
	}
}

class EntriesControllerTestCase extends SaitoControllerCakeTestCase {
	var $fixtures = array('app.user', 'app.user_online', 'app.entry', 'app.category', 'app.smiley', 'app.smiley_code', 'app.setting', 'app.upload');
	public $name = 'Entries';

	function testIndex() {

		//* not logged in user
		$this->_prepareAction('/entries/index');
		$result = $this->testAction('/entries/index', array( 'return' => 'vars' ));
		$entries = $result['entries'];
		$this->assertEqual(count($entries), 1);

		//* logged in user
		$this->_loginUser(3);
		$this->_prepareAction('/entries/index');
		$result = $this->testAction('/entries/index', array( 'return' => 'vars' ));
		$entries = $result['entries'];
		$this->assertEqual(count($entries), 2);
		
	}

	function testView() {

		//* not logged in user
		$this->_prepareAction('/entries/view/1');
		$result = $this->testAction('/entries/view/1', array( 'return' => 'vars' ));
		$this->assertEqual($result['entry']['Entry']['id'], 1);

		$this->Entries->view(2);
		$this->assertNull($this->Entries->redirectUrl);

		$this->Entries->view(4);
		$this->assertEqual($this->Entries->redirectUrl, '/');
		$this->Entries->redirectUrl = NULL;

		//* logged in user
		$this->_loginUser(3);
		$this->_prepareAction('/entries/view/4');
		$result = $this->testAction('/entries/view/4', array( 'return' => 'vars' ));
		$this->assertEqual($result['entry']['Entry']['id'], 4);

		$this->Entries->view(2);
		$this->assertNull($this->Entries->redirectUrl);

		$this->Entries->view(4);
		$this->assertNull($this->Entries->redirectUrl);

		//* redirect to index if entry does not exist
		$this->Entries->redirectUrl = NULL;
		$this->_prepareAction('/entries/view/9999');
		$this->Entries->view(9999);
		$this->assertEqual($this->Entries->redirectUrl, array(0 => '/'));

	}

	public function testHeaderCounter() {

		$this->_prepareAction('/entries/index');

		//* test with no user online
		$result = $this->testAction('/entries/index', array('return'=>'vars'));
		$headerCounter = $result['HeaderCounter'];

		$this->assertEqual($headerCounter['user_online'], 1);
		$this->assertEqual($headerCounter['user'], 4);
		$this->assertEqual($headerCounter['entries'], 4);
		$this->assertEqual($headerCounter['threads'], 2);
		$this->assertEqual($headerCounter['user_registered'], 0);
		$this->assertEqual($headerCounter['user_anonymous'], 1);


		//* test with one user online
		$this->_loginUser(2);

		$result = $this->testAction('/entries/index', array('return'=>'vars'));
		$headerCounter = $result['HeaderCounter'];

		$this->assertEqual($headerCounter['user_online'], 2);
		$this->assertEqual($headerCounter['user_registered'], 1);
		$this->assertEqual($headerCounter['user_anonymous'], 1);

	}

	function testGetViewVarsSearch() {
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


	function startTest($message) {
		$this->Entries =& new TestEntriesController();
		$this->Entries->constructClasses();

		$this->_saito_settings_topics_per_page = Configure::read('Saito.Settings.topics_per_page');
		Configure::write('Saito.Settings.topics_per_page', 20);
	}

	function endTest() {
		$this->Entries->Session->destroy();
		unset($this->Entries);
		ClassRegistry::flush();

		Configure::write('Saito.Settings.topics_per_page', $this->_saito_settings_topics_per_page);
	}

}
?>