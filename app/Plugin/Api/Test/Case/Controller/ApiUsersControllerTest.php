<?php

	App::uses('ApiControllerTestCase', 'Api.Lib');

	/**
	 * ApiUsersController Test Case
	 *
	 */
	class ApiUsersControllerTest extends ApiControllerTestCase {

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
			'plugin.api.upload',
			'plugin.api.setting',
			'plugin.api.ecach'
		);

		public function testLoginNoUsername() {
			$this->expectException(
				'BadRequestException',
				'Field `username` is missing.'
			);
			$this->testAction($this->apiRoot . 'login', ['method' => 'POST']);
		}

		public function testLoginNoPassword() {
			$this->expectException(
				'BadRequestException',
				'Field `password` is missing.'
			);
			$data = [
				'username' => 'Jane'
			];
			$this->testAction(
				$this->apiRoot . 'login',
				['data' => $data, 'method' => 'POST']
			);
		}

		public function testLoginSuccess() {
			$data = [
				'username' => 'Alice',
				'password' => 'test',
				'remember_me' => '1'
			];

			$expected = json_decode('
				{
					"user": {
						"isLoggedIn": true,
						"id": 1,
						"last_refresh": "1970-01-01T00:00:00+00:00",
						"threads_order": "time"
					}
				}
			');

			$result = $this->testAction(
				$this->apiRoot . 'login.json',
				['method' => 'POST', 'data' => $data, 'return' => 'contents']
			);
			$this->assertEqual(json_decode($result), $expected);
		}

		public function testLoginFailure() {
			$data = [
				'username' => 'Jane',
				'password' => 'N7',
				'remember_me' => '1'
			];
			$ApiUsers = $this->generate(
				'ApiUsers',
				['components' => ['CurrentUser' => ['login', 'initialize', 'isLoggedIn']]]
			);
			$ApiUsers->CurrentUser->expects($this->once())
					->method('login');
			$ApiUsers->CurrentUser->expects($this->any())
					->method('isLoggedIn')
					->will($this->returnValue(false));
			$this->expectException('UnauthorizedException');
			$this->testAction(
				$this->apiRoot . 'login',
				['method' => 'POST', 'data' => $data]
			);
		}

		public function testLoginDisallowedRequestType() {
			$this->_checkDisallowedRequestType(
				['GET', 'PUT', 'DELETE'],
					$this->apiRoot . 'login'
			);
		}

		public function testMarkAsReadMissingUserId() {
			$this->generate('Api.ApiUsers');
			$this->_loginUser(3);
			$data = [ ];
			$this->expectException('InvalidArgumentException', 'User id is missing.');
			$this->testAction(
				$this->apiRoot . 'markasread',
				['method' => 'POST', 'data' => $data]
			);
		}

		public function testMarkAsReadUserIdNotAuthorized() {
			$this->generate('Api.ApiUsers');
			$this->_loginUser(3);
			$data = [
				'id' => 1
			];
			$this->expectException('ForbiddenException', 'You are not authorized for user id `1`.');
			$this->testAction(
				$this->apiRoot . 'markasread',
				['method' => 'POST', 'data' => $data]
			);
		}

		public function testMarkAsReadSuccessNow() {
			$ApiUsers = $this->generate(
				'ApiUsers',
				[
					'components' => [
						'CurrentUser' => [
							'isLoggedIn',
							'getId',
							'initialize'
						]
					]
				]
			);

			$user_id = 3;

			$ApiUsers->CurrentUser->expects($this->any())
					->method('isLoggedIn')
					->will($this->returnValue(true));
			$ApiUsers->CurrentUser->expects($this->any())
					->method('getId')
					->will($this->returnValue($user_id));


			$ApiUsers->CurrentUser->LastRefresh = $this->getMock('Object', ['set']);
			$ApiUsers->CurrentUser->LastRefresh->expects($this->once())
					->method('set')
					->with('now');

			$this->_loginUser($user_id);
			$data = [
				'id' => $user_id
			];

			$result = $this->testAction(
				$this->apiRoot . 'markasread.json',
				['method' => 'POST', 'data' => $data, 'return' => 'contents']
			);
			$result = json_decode($result, true);
			$this->assertTrue(isset($result['last_refresh']));
		}

		public function testMarkAsReadSuccessTimestamp() {
			$ApiUsers = $this->generate(
				'ApiUsers',
				[
					'components' => [
						'CurrentUser' => [
							'isLoggedIn',
							'getId',
							'initialize'
						]
					]
				]
			);

			$user_id = 3;

			$ApiUsers->CurrentUser->expects($this->any())
					->method('isLoggedIn')
					->will($this->returnValue(true));
			$ApiUsers->CurrentUser->expects($this->any())
					->method('getId')
					->will($this->returnValue($user_id));


			$ApiUsers->CurrentUser->LastRefresh = $this->getMock('Object', ['set']);
			$ApiUsers->CurrentUser->LastRefresh->expects($this->once())
					->method('set')
					->with('2013-07-04 19:53:14');

			$this->_loginUser($user_id);
			$data = [
				'id'      => $user_id,
				'last_refresh' => '2013-07-04T19:53:14+00:00'
			];

			$result = $this->testAction(
				$this->apiRoot . 'markasread.json',
				['method' => 'POST', 'data' => $data, 'return' => 'contents']
			);
			$result = json_decode($result, true);
			$this->assertTrue(isset($result['last_refresh']));
		}

		/**
		 * Send timestamp is ignored is not set if it's older than the current one
		 */
		public function testMarkAsReadNoPastValues() {
			$ApiUsers = $this->generate('ApiUsers');

			$user_id = 3;
			$this->_loginUser($user_id);

			$ApiUsers->User->id = $user_id;
			$ApiUsers->User->saveField('last_refresh', '2013-07-04 19:53:14');

			$data = [
				'id'      => $user_id,
				'last_refresh' => '2013-07-04T19:53:13+00:00'
			];

			$result = $this->testAction(
				$this->apiRoot . 'markasread.json',
				['method' => 'POST', 'data' => $data, 'return' => 'contents']
			);
			$result = json_decode($result, true);
			$expected = [
				'last_refresh' => '2013-07-04T19:53:14+00:00'
			];
			$this->assertEqual($result, $expected);
		}

		public function testMarkAsReadOnlyAuthenticatedUsers() {
			$this->generate('ApiUsers', ['methods' => 'markasread']);
			$this->testAction($this->apiRoot . 'markasread.json', ['method' => 'POST']);
			$this->assertRedirectedTo('login');
		}

		public function testMarkAsReadDisallowedRequestType() {
			$this->_checkDisallowedRequestType(
				['GET', 'PUT', 'DELETE'],
					$this->apiRoot . 'markasread'
			);
		}

	}
