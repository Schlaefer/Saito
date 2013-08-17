<?php

	App::uses('ApiAppController', 'Api.Controller');

	class ApiUsersController extends ApiAppController {

		public $uses = [
			'User'
		];

		public $helpers = [
			'Api.Api'
		];

		public $saveKeysToOutput = [];

		public function login() {

			if (isset($this->request->data['username']) === false) {
				throw new BadRequestException('Field `username` is missing.');
			}

			if (isset($this->request->data['password']) === false) {
				throw new BadRequestException('Field `password` is missing.');
			}

			$this->request->data = [
				'User' => [
					'username'    => $this->request->data['username'],
					'password'    => $this->request->data['password'],
					'remember_me' => empty($this->request->data['remember_me']) ? false : true
				]
			];

			$this->CurrentUser->login();

			if ($this->CurrentUser->isLoggedIn() !== true) {
				throw new UnauthorizedException(
					'Login failed. Check your username and password.'
				);
			}
		}

		public function logout() {
			if (!$this->CurrentUser->isLoggedIn()) {
				throw new ForbiddenException('You are not logged in.');
			}
			if (!isset($this->request->data['id'])) {
				throw new BadRequestException('User id is missing.');
			}
			$user_id = $this->request->data['id'];
			if ((int)$user_id !== $this->CurrentUser->getId()) {
				throw new ForbiddenException(sprintf(
					'Not allowed to logout user with id `%s`.',
					$user_id
				));
			}
			$this->CurrentUser->logout();
		}

		public function markasread() {
			if (!isset($this->request->data['id'])) {
				throw new InvalidArgumentException('User id is missing.');
			}
			$user_id = $this->request->data['id'];
			if (!$this->CurrentUser->isLoggedIn() ||
					$this->CurrentUser->getId() != $user_id
			) {
				throw new ForbiddenException(sprintf(
					'You are not authorized for user id `%s`.',
					$user_id
				));
			}
			if (isset($this->request->data['last_refresh'])) {
				$timestamp = strtotime($this->request->data['last_refresh']);
				if ($timestamp === false) {
					throw new InvalidArgumentException(sprintf(
						'`%s` is not a valid timestamp string.',
						$timestamp
					));
				}
				$isServerTimestampNewer = strtotime($this->CurrentUser['last_refresh'])
						> $timestamp;
				if ($isServerTimestampNewer === false) {
					$this->CurrentUser->LastRefresh->set(date("Y-m-d H:i:s", $timestamp));
				}
			} else {
				$this->CurrentUser->LastRefresh->set('now');
			}
			$this->set('id', $user_id);
			$this->set('last_refresh', $this->CurrentUser['last_refresh']);
		}

		public function beforeFilter() {
			parent::beforeFilter();
			$this->Auth->allow('login', 'logout');
		}

	}
