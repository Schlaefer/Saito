<?php
/* Settings Test cases generated on: 2010-06-22 13:06:06 : 1277204826*/
App::import('Controller', 'Settings');

class TestSettingsController extends SettingsController {
	var $autoRender = false;

	function redirect($url, $status = null, $exit = true) {
		$this->redirectUrl = $url;
	}
}

class SettingsControllerTest extends CakeTestCase {
	var $fixtures = array('app.setting');

	function startTest($message) {
		$this->Settings =& new TestSettingsController();
		$this->Settings->constructClasses();
	}

	function endTest() {
		unset($this->Settings);
		ClassRegistry::flush();
	}

}
?>