<?php

	class AllControllerTest extends CakeTestSuite {

		public static function suite() {
			$suite = new CakeTestSuite('All controller and component tests.');
			$suite->addTestDirectory(TESTS . 'Case' . DS . 'Controller');
			return $suite;
		}

	}

?>