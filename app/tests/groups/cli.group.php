<?php
/**
 * All tests without Web Tests
 */
class CliGroupTest extends TestSuite {
	public $label = 'All App Helpers';

	function CliGroupTest() {
		TestManager::addTestCasesFromDirectory($this, APP_TEST_CASES . DS . 'behaviors');
		TestManager::addTestCasesFromDirectory($this, APP_TEST_CASES . DS . 'components');
//		TestManager::addTestCasesFromDirectory($this, APP_TEST_CASES . DS . 'controllers');
		TestManager::addTestCasesFromDirectory($this, APP_TEST_CASES . DS . 'libs');
		TestManager::addTestCasesFromDirectory($this, APP_TEST_CASES . DS . 'models');
		TestManager::addTestCasesFromDirectory($this, APP_TEST_CASES . DS . 'helpers');
	}
}
?>
