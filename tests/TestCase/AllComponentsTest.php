<?php

class AllComponentsTest extends PHPUnit_Framework_TestSuite {

/**
 * suite method, defines tests for this suite.
 *
 * @return void
 */
	public static function suite() {
		$suite = new CakeTestSuite('All component class tests');

		$suite->addTestDirectoryRecursive(TESTS . DS . 'Case' . DS . 'Controller' . DS . 'Component');
		return $suite;
	}
}
