<?php

	App::uses('ApiControllerTestCase', 'Api.Lib');

/**
 * ApiUsersController Test Case
 *
 */
	class ApiUsersControllerTest extends ApiControllerTestCase {

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
			$this->testAction($this->_apiRoot . 'login', ['method' => 'POST']);
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
				$this->_apiRoot . 'login',
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
						"username": "Alice",
						"last_refresh": "1970-01-01T00:00:00+00:00",
						"threads_order": "time"
					}
				}
			');

			$result = $this->testAction(
				$this->_apiRoot . 'login.json',
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
				[
					'components' => [
						'CurrentUser' => [
							'login',
							'initialize',
							'isLoggedIn',
							'logout'
						]
					]
				]
			);
			$ApiUsers->CurrentUser->expects($this->once())
					->method('logout');
			$ApiUsers->CurrentUser->expects($this->once())
					->method('login');
			$ApiUsers->CurrentUser->expects($this->any())
					->method('isLoggedIn')
					->will($this->returnValue(false));
			$this->expectException('UnauthorizedException');
			$this->testAction(
				$this->_apiRoot . 'login',
				['method' => 'POST', 'data' => $data]
			);
		}

		public function testLoginDisallowedRequestType() {
			$this->_checkDisallowedRequestType(
				['GET', 'PUT', 'DELETE'],
					$this->_apiRoot . 'login'
			);
		}

		public function testMarkAsReadMissingUserId() {
			$this->generate('Api.ApiUsers');
			$this->_loginUser(3);
			$data = [ ];
			$this->expectException('InvalidArgumentException', 'User id is missing.');
			$this->testAction(
				$this->_apiRoot . 'markasread',
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
				$this->_apiRoot . 'markasread',
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

			$_userId = 3;

			$ApiUsers->CurrentUser->expects($this->any())
					->method('isLoggedIn')
					->will($this->returnValue(true));
			$ApiUsers->CurrentUser->expects($this->any())
					->method('getId')
					->will($this->returnValue($_userId));

			$ApiUsers->CurrentUser->LastRefresh = $this->getMock('Object', ['set']);
			$ApiUsers->CurrentUser->LastRefresh->expects($this->once())
					->method('set')
					->with('now');

			$this->_loginUser($_userId);
			$data = [
				'id' => $_userId
			];

			$result = $this->testAction(
				$this->_apiRoot . 'markasread.json',
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

			$_userId = 3;

			$ApiUsers->CurrentUser->expects($this->any())
					->method('isLoggedIn')
					->will($this->returnValue(true));
			$ApiUsers->CurrentUser->expects($this->any())
					->method('getId')
					->will($this->returnValue($_userId));

			$ApiUsers->CurrentUser->LastRefresh = $this->getMock('Object', ['set']);
			$ApiUsers->CurrentUser->LastRefresh->expects($this->once())
					->method('set')
					->with('2013-07-04 19:53:14');

			$this->_loginUser($_userId);
			$data = [
				'id' => $_userId,
				'last_refresh' => '2013-07-04T19:53:14+00:00'
			];

			$result = $this->testAction(
				$this->_apiRoot . 'markasread.json',
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

			$_userId = 3;
			$this->_loginUser($_userId);

			$ApiUsers->User->id = $_userId;
			$ApiUsers->User->saveField('last_refresh', '2013-07-04 19:53:14');

			$data = [
				'id' => $_userId,
				'last_refresh' => '2013-07-04T19:53:13+00:00'
			];

			$result = $this->testAction(
				$this->_apiRoot . 'markasread.json',
				['method' => 'POST', 'data' => $data, 'return' => 'contents']
			);
			$result = json_decode($result, true);
			$expected = [
				'last_refresh' => '2013-07-04T19:53:14+00:00'
			] + $data;
			$this->assertEqual($result, $expected);
		}

		public function testMarkAsReadOnlyAuthenticatedUsers() {
			$this->generate('ApiUsers', ['methods' => 'markasread']);
			$this->testAction($this->_apiRoot . 'markasread.json', ['method' => 'POST']);
			$this->assertRedirectedTo('login');
		}

		public function testMarkAsReadDisallowedRequestType() {
			$this->_checkDisallowedRequestType(
				['GET', 'PUT', 'DELETE'],
					$this->_apiRoot . 'markasread'
			);
		}

	}
