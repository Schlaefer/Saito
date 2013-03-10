<?php

	App::uses('UploadsController', 'Controller');
	App::uses('SaitoControllerTestCase', 'Lib');

	/**
	 * UploadsController Test Case
	 *
	 */
	class UploadsControllerTest extends SaitoControllerTestCase {

		/**
		 * Fixtures
		 *
		 * @var array
		 */
		public $fixtures = array(
			'app.upload',
			'app.user',
			'app.user_online',
			'app.bookmark',
			'app.entry',
			'app.category',
			'app.esevent',
			'app.esnotification',
			'app.setting'
		);

		public function testAddUserMustBeLoggedIn() {
			$this->expectException('ForbiddenException');
			$this->testAction('/uploads/add');
		}

		public function testAddMustBePost() {
			$this->generate('Uploads');
			$this->_loginUser(1);
			$this->expectException('MethodNotAllowedException');
			$this->testAction('/uploads/add', array('method' => 'get'));
		}

		/*
		public function testAddMaxFilesReached() {
			$this->expectException('BadRequestException');
			$this->testAction('/uploads/add', array('method' => 'get'));
		}
		*/

		/**
		 * testAdd method
		 *
		 * @return void
		 */
		public function tes1Add() {
		}

		/**
		 * testIndex method
		 *
		 * @return void
		 */
		public function testIndexUserMustBeLoggedIn() {
			$this->expectException('ForbiddenException');
			$this->testAction('/uploads/index');
		}

		public function testIndexCallMustBeAjax() {
			$this->generate('Uploads');
			$this->_loginUser(1);
			$this->expectException('BadRequestException');
			$this->testAction('/uploads/index', array('method' => 'get'));
		}

		public function testIndex() {
			$this->_setAjax();
			$this->_setJson();

			$this->generate('Uploads');
			$this->_loginUser(3);
			$result = $this->testAction(
				'/uploads/index', array('return' => 'contents'));
			;
			$result = json_decode($result);
			$this->assertCount(1, $result);
			$this->assertEqual(
				$result[0]->name, '3_upload_test.png');
			$this->assertEqual(
				$result[0]->id, 1);
		}

		/*
		public function testIndexUserMustBeLoggedIn() {
			$this->testAction('/uploads/index', array('method' => 'get'));
			$this->assertEqual(FULL_BASE_URL . $this->controller->request->webroot . 'login', $this->headers['Location']);
		}
		*/

		public function testDeleteUserMustBeLoggedIn() {
			$this->expectException('ForbiddenException');
			$this->testAction('/uploads/delete');
		}

		/**
		 * testDelete method
		 *
		 * @return void
		 */
		public function testDelete() {
		}

	}
