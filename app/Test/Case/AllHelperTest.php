<?php

	class AllHelperTest extends CakeTestSuite {

		public static function suite() {
			$suite = new CakeTestSuite('All helper tests.');
			$suite->addTestDirectory(TESTS . 'Case' . DS . 'View' . DS . 'Helper');
			return $suite;
		}

	}
