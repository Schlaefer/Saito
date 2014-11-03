<?php

	class AllTest extends CakeTestSuite {

		public static function suite() {
			$suite = new CakeTestSuite('All tests.');

			//= core
			$suite->addTestDirectoryRecursive(TESTS . 'Case' . DS . 'Controller' . DS . 'Component');
			$suite->addTestDirectory(TESTS . 'Case' . DS . 'Controller');
			$suite->addTestDirectoryRecursive(TESTS . 'Case' . DS . 'Lib');
			$suite->addTestDirectory(TESTS . 'Case' . DS . 'Model' . DS . 'Behavior');
			$suite->addTestDirectory(TESTS . 'Case' . DS . 'Model');
			$suite->addTestDirectory(TESTS . 'Case' . DS . 'View' . DS . 'Helper');

			//= plugins
			$suite->addTestDirectoryRecursive(CakePlugin::path('Api') . 'Test');
			$suite->addTestDirectoryRecursive(CakePlugin::path('BbcodeParser') . 'Test');
			$suite->addTestDirectoryRecursive(CakePlugin::path('Sitemap') . 'Test');

			return $suite;
		}

	}
