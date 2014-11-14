<?php

	App::uses('Controller', 'Controller');
	App::uses('View', 'View');
	App::uses('UserHHelper', 'View/Helper');

	class UserHHelperTest extends CakeTestCase {

		public function testDummy() {
		}

		public function setUp() {
			parent::setUp();
			$Controller = new Controller();
			$View = new View($Controller);
			$this->UserH = new UserHHelper($View);
		}

		public function tearDown() {
			parent::tearDown();
			unset($this->UserH);
			ClassRegistry::flush();
		}

	}

