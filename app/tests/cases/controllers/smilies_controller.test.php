<?php
/* Smilies Test cases generated on: 2011-05-17 12:27:05 : 1305628025*/
App::import('Controller', 'Smilies');

class TestSmiliesController extends SmiliesController {
	var $autoRender = false;

	function redirect($url, $status = null, $exit = true) {
		$this->redirectUrl = $url;
	}
}

class SmiliesControllerTestCase extends CakeTestCase {
	var $fixtures = array('app.smiley', 'app.smiley_code', 'app.user', 'app.user_online', 'app.entry', 'app.category', 'app.upload');

	function startTest() {
		$this->Smilies =& new TestSmiliesController();
		$this->Smilies->constructClasses();
	}

	function endTest() {
		unset($this->Smilies);
		ClassRegistry::flush();
	}

	function testAdminIndex() {

	}

	function testAdminView() {

	}

	function testAdminAdd() {

	}

	function testAdminEdit() {

	}

	function testAdminDelete() {

	}

}
?>