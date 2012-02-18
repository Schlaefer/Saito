<?php

	class AllModelTest extends CakeTestSuite {

		public static function suite() {
			$suite = new CakeTestSuite('All model tests.');
			$suite->addTestDirectory(TESTS . 'Case' . DS . 'Model');
			return $suite;
		}

	}

?>