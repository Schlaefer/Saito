<?php

	App::uses('ApiControllerTestCase', 'Api.Lib');

	/**
	 * ApiEntriesController Test Case
	 *
	 */
	class ApiShoutsControllerTest extends ApiControllerTestCase {

		protected $_apiRoot = 'api/v1/';

		protected $_fixtureResult = [
			0 =>
					array(
						'id' => 4,
						'time' => '2013-02-08T11:49:31+00:00',
						'text' => '<script></script>[i]italic[/i]',
						'html' => '&lt;script&gt;&lt;/script&gt;<em>italic</em>',
						'user_id' => 1,
						'user_name' => 'Alice',
					),
			1 =>
					array(
						'id' => 3,
						'time' => '2013-02-08T11:49:31+00:00',
						'text' => 'Lorem ipsum dolor sit amet',
						'html' => 'Lorem ipsum dolor sit amet',
						'user_id' => 1,
						'user_name' => 'Alice',
					),
			2 =>
					array(
						'id' => 2,
						'time' => '2013-02-08T11:49:31+00:00',
						'text' => 'Lorem ipsum dolor sit amet',
						'html' => 'Lorem ipsum dolor sit amet',
						'user_id' => 1,
						'user_name' => 'Alice',
					),
		];

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
			'plugin.api.setting',
			'plugin.api.smiley',
			'plugin.api.smiley_code',
			'plugin.api.shout',
			'plugin.api.upload'
		);

		public function testShoutsDisallowedRequestTypes() {
			$this->_checkDisallowedRequestType(
				['PUT', 'DELETE'],
					$this->_apiRoot . 'shouts'
			);
		}

		public function testShoutsGet() {
			$this->generate('Api.ApiShouts');
			$this->_loginUser(3);

			$expected = $this->_fixtureResult;
			$result = $this->testAction($this->_apiRoot . 'shouts.json',
				[
					'method' => 'GET',
					'return' => 'contents'
				]);
			$result = json_decode($result, true);
			$this->assertEqual($result, $expected);
		}

		public function testShoutsGetNotLoggedIn() {
			$this->generate('Api.ApiShouts');
			$this->setExpectedException('Saito\Api\ApiAuthException');
			$this->testAction($this->_apiRoot . 'shouts.json', ['method' => 'GET']);
		}

		public function testShoutsPost() {
			$this->generate('Api.ApiShouts');
			$this->_loginUser(3);

			$data = [
				'text' => 'test < shout'
			];
			$result = $this->testAction($this->_apiRoot . 'shouts.json',
				[
					'method' => 'POST',
					'data' => $data,
					'return' => 'contents'
				]);
			$result = json_decode($result, true);
			$_newEntry = array_shift($result);

			$_newEntryTime = strtotime($_newEntry['time']);
			$this->assertGreaterThanOrEqual(time() - 1, $_newEntryTime);
			unset($_newEntry['time']);

			$expected = [
				'id' => 5,
				'text' => 'test < shout',
				'html' => 'test &lt; shout',
				'user_id' => 3,
				'user_name' => 'Ulysses',
			];
			$this->assertEqual($_newEntry, $expected);

			$expected = $this->_fixtureResult;
			$this->assertEqual($result, $expected);
		}

		public function testShoutsPostTextMissing() {
			$this->generate('Api.ApiShouts');
			$this->_loginUser(3);

			$this->setExpectedException('BadRequestException', 'Missing text.');
			$this->testAction($this->_apiRoot . 'shouts.json', ['method' => 'POST']);
		}

		public function testShoutsPostNotLoggedIn() {
			$this->generate('Api.ApiShouts');
			$this->setExpectedException('Saito\Api\ApiAuthException');
			$this->testAction($this->_apiRoot . 'shouts.json', ['method' => 'POST']);
		}

	}
