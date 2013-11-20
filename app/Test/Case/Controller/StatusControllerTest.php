<?php

	App::uses('SaitoControllerTestCase', 'Lib');

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
			'app.user_online',
			'app.bookmark',
			'app.entry',
			'app.category',
			'app.esevent',
			'app.esnotification',
			'app.upload',
			'app.setting'
		);

		public function testStatusMustBeAjax() {
			$this->expectException('BadRequestException');
			$this->testAction('/status/status');
		}

		public function testStatusIfNotLoggedIn() {
			$this->_setJson();
			$this->_setAjax();
			$this->testAction('/status/status');
			$this->assertFalse(isset($this->headers['Location']));
		}

		public function testStatusSuccess() {
			$this->_setJson();
			$this->_setAjax();
			$expected = json_encode(
				array(
					'lastShoutId' => 4
				)
			);
			$result = $this->testAction('/status/status', ['return' => 'contents']);
			$this->assertEqual($result, $expected);
		}

	}
