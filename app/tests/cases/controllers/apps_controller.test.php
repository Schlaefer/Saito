<?php
/* Entries Test cases generated on: 2010-07-08 18:07:45 : 1278607425*/
App::import('Controller', 'App');
require_once '_saito_controller_test_case.php';

if (!defined('FULL_BASE_URL')) {
	define('FULL_BASE_URL', 'http://cakephp.org/');
}

class TestAppsController extends AppController {
	var $autoRender = false;
	var $uses = null;

	function redirect($url, $status = null, $exit = true) {
		$this->redirectUrl = $url;
	}
}

class AppsControllerTestCase extends SaitoControllerCakeTestCase {
	var $fixtures = array('app.user', 'app.user_online', 'app.entry', 'app.category', 'app.smiley', 'app.smiley_code', 'app.setting', 'app.upload');
	public $name = 'Apps';

	public function testCurrentUser() {

		//* check there's no current user
		$this->_prepareAction('/entries/index');
		$result = $this->testAction('/entries/index', array( 'return' => 'vars' ));

		$this->assertFalse($result['CurrentUser']->getId());
		$this->assertFalse($result['CurrentUser']->isLoggedIn());

		//* loginUser
		$this->_loginUser(3);
		$this->_prepareAction('/entries/index');
		$result = $this->testAction('/entries/index', array( 'return' => 'vars' ));

		//* check there's no current user
		$this->assertEqual($result['CurrentUser']->getId(), 3);
		$this->assertTrue($result['CurrentUser']->isLoggedIn());
	}


	function testLocalReferer() {
		$_SERVER['HTTP_REFERER'] = FULL_BASE_URL.$this->Apps->webroot.'/entries/index';
		$result = $this->Apps->localReferer();
		$expected = '/entries/index';
		$this->assertIdentical($result, $expected);

		$_SERVER['HTTP_REFERER'] = FULL_BASE_URL.$this->Apps->webroot.'/entries/view';
		$result = $this->Apps->localReferer('action');
		$expected = 'view';
		$this->assertIdentical($result, $expected);

		$_SERVER['HTTP_REFERER'] = FULL_BASE_URL.$this->Apps->webroot.'/some/path';
		$result = $this->Apps->localReferer('controller');
		$expected = 'some';
		$this->assertIdentical($result, $expected);

		$_SERVER['HTTP_REFERER'] = FULL_BASE_URL.$this->Apps->webroot.'/some/';
		$result = $this->Apps->localReferer('action');
		$expected = 'index';
		$this->assertIdentical($result, $expected);

		$_SERVER['HTTP_REFERER'] = FULL_BASE_URL.$this->Apps->webroot.'';
		$result = $this->Apps->localReferer('action');
		$expected = 'index';
		$this->assertIdentical($result, $expected);

		$_SERVER['HTTP_REFERER'] = FULL_BASE_URL.$this->Apps->webroot.'';
		$result = $this->Apps->localReferer('controller');
		$expected = 'entries';
		$this->assertIdentical($result, $expected);

		//* external referer
		$_SERVER['HTTP_REFERER'] = 'http://heise.de/foobar/baz.html';
		$result = $this->Apps->localReferer('controller');
		$expected = 'entries';
		$this->assertIdentical($result, $expected);

		$_SERVER['HTTP_REFERER'] = 'http://heise.de/foobar/baz.html';
		$result = $this->Apps->localReferer('action');
		$expected = 'index';
		$this->assertIdentical($result, $expected);
	}

	//-----------------------------------------------

	function startCase() {
	}

	function endCase() {
	}

	function startTest($message) {
		$this->Apps =& new TestAppsController();
		$this->Apps->constructClasses();
	}

	function endTest() {
		$this->Apps->Session->destroy();
		unset($this->Apps);
		ClassRegistry::flush();
	}
}
?>