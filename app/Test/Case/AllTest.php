<?php

	class AllTest extends CakeTestSuite {

		public static function suite() {
			$suite = new CakeTestSuite('All tests.');
			$suite->addTestDirectory(TESTS . 'Case');
			return $suite;
		}

	}

?>