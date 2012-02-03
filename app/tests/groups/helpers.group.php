<?php
class HelpersGroupTest extends TestSuite {
	public $label = 'All App Helpers';

	function HelpersGroupTest() {
		TestManager::addTestCasesFromDirectory($this, APP_TEST_CASES . DS . 'helpers');
	}
}
?>
