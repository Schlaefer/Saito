<?php

	class AllModelTest extends CakeTestSuite {

		public static function suite() {
			$suite = new CakeTestSuite('All lib tests.');
			$suite->addTestDirectoryRecursive(TESTS . 'Case' . DS . 'Lib');
			return $suite;
		}

	}
