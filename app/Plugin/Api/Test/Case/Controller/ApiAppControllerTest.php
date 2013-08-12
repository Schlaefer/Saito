<?php

	App::uses('ApiControllerTestCase', 'Api.Lib');

	/**
	 * ApiUsersController Test Case
	 *
	 */
	class ApiAppControllerTest extends ApiControllerTestCase {

		protected $apiRoot = 'api/v1/';

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
			'plugin.api.bookmark',
			'plugin.api.esnotification',
			'plugin.api.esevent',
			'plugin.api.ecach',
			'plugin.api.upload',
			'plugin.api.setting'
		);

		public function testApiDisabled() {
			Configure::write('Saito.Settings.api_enabled', '0');
			$this->expectException('Saito\Api\ApiDisabledException');
			$this->testAction($this->apiRoot . 'bootstrap.json');
		}

		public function testApiEnabled() {
			Configure::write('Saito.Settings.api_enabled', '1');
			$this->expectException('Saito\Api\UnknownRouteException');
			$this->testAction($this->apiRoot . 'foobar');
		}

		public function testApiAllowOriginHeader() {
			$expected = rand();
			Configure::write('Saito.Settings.api_crossdomain', $expected);
			$Controller = $this->generate('ApiCore');
			$this->testAction(
				$this->apiRoot . 'bootstrap.json',
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
				$this->apiRoot . 'bootstrap.json',
				[
					'method' => 'GET',
					'return' => 'contents'
				]
			);
			$headers = $Controller->response->header();
			$this->assertFalse(isset($headers['Access-Control-Allow-Origin']));
		}
	}
