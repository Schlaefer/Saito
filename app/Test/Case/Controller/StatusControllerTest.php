<?php

	App::uses('SaitoControllerTestCase', 'Lib/Test');

	/**
	 * ShoutsController Test Case
	 *
	 */
	class StatusControllerTest extends SaitoControllerTestCase {

		/**
		 * Fixtures
		 *
		 * @var array
		 */
		public $fixtures = array(
			'app.shout',
			'app.user',
			'app.user_block',
			'app.user_online',
			'app.user_read',
			'app.bookmark',
			'app.entry',
			'app.category',
			'app.esevent',
			'app.esnotification',
			'app.upload',
			'app.setting'
		);

		public function testStatusMustBeAjax() {
			$this->setExpectedException('BadRequestException');
			$this->testAction('/status/status', ['method' => 'GET']);
		}

		public function testStatusIfNotLoggedIn() {
			$this->_setJson();
			$this->_setAjax();
			$this->testAction('/status/status', ['method' => 'GET']);
			$this->assertFalse(isset($this->headers['Location']));
		}

		public function testStatusSuccess() {
			$this->_setJson();
			$this->_setAjax();
			$expected = json_encode(['lastShoutId' => 4]);
			$result = $this->testAction('/status/status',
				['method' => 'GET', 'return' => 'contents']);
			$this->assertEquals($result, $expected);
		}

	}
