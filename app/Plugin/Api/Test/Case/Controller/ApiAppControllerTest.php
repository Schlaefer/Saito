<?php

	App::uses('ApiControllerTestCase', 'Api.Lib');

/**
 * ApiUsersController Test Case
 *
 */
	class ApiAppControllerTest extends ApiControllerTestCase {

		protected $_apiRoot = 'api/v1/';

/**
 * Fixtures
 *
 * @var array
 */
		public $fixtures = array(
			'plugin.api.entry',
			'plugin.api.category',
			'plugin.api.user',
			'plugin.api.user_online',
			'plugin.api.user_read',
			'plugin.api.bookmark',
			'plugin.api.esnotification',
			'plugin.api.esevent',
			'plugin.api.ecach',
			'plugin.api.upload',
			'plugin.api.setting'
		);

		public function testApiDisabled() {
			Configure::write('Saito.Settings.api_enabled', '0');
			$this->setExpectedException('Saito\Api\ApiDisabledException');
			$this->testAction($this->_apiRoot . 'bootstrap.json');
		}

		public function testApiEnabled() {
			Configure::write('Saito.Settings.api_enabled', '1');
			$this->setExpectedException('Saito\Api\UnknownRouteException');
			$this->testAction($this->_apiRoot . 'foobar');
		}

		public function testApiAllowOriginHeader() {
			$expected = rand();
			Configure::write('Saito.Settings.api_crossdomain', $expected);
			$Controller = $this->generate('ApiCore');
			$this->testAction(
				$this->_apiRoot . 'bootstrap.json',
				[
					'method' => 'GET',
					'return' => 'contents'
				]
			);
			$header = $Controller->response->header()['Access-Control-Allow-Origin'];
			$this->assertEqual($header, $expected);
		}

		public function testApiAllowOriginHeaderNotSet() {
			Configure::write('Saito.Settings.api_crossdomain', '');
			$Controller = $this->generate('ApiCore');
			$this->testAction(
				$this->_apiRoot . 'bootstrap.json',
				[
					'method' => 'GET',
					'return' => 'contents'
				]
			);
			$headers = $Controller->response->header();
			$this->assertFalse(isset($headers['Access-Control-Allow-Origin']));
		}

	}
