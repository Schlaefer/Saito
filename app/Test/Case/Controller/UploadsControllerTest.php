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

		public function testAddMaxUploadsReached() {
			$max_uploads = Configure::read(
				'Saito.Settings.upload_max_number_of_uploads'
			);

			$current_uploads = 10;
			Configure::write(
				'Saito.Settings.upload_max_number_of_uploads',
				$current_uploads
			);

			$Uploads = $this->generate(
				'Uploads',
				array(
					'models' => array(
						'Upload' => array(
							'countUser',
							'create'
						)
					)
				)
			);

			$this->_loginUser(1);

			$Uploads->Upload->expects($this->once())
				->method('countUser')
				->with(1)
				->will($this->returnValue($current_uploads));

			$Uploads->Upload->expects($this->never())
				->method('create');

			$result = $this->testAction(
				'/uploads/add',
				array(
					'method' => 'post',
					'return' => 'contents'
				)
			);
			$result = json_decode($result);

			$first_message =  $result->msg[0];
			$this->assertEqual($first_message->type, 'error');

			Configure::write(
				'Saito.Settings.upload_max_number_of_uploads',
				$max_uploads
			);
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
