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
			'app.user_ignore',
			'app.user_online',
			'app.user_read',
			'app.bookmark',
			'app.entry',
			'app.category',
			'app.esevent',
			'app.esnotification',
			'app.setting'
		);

		public function testAddUserMustBeLoggedIn() {
			$this->setExpectedException('ForbiddenException');
			$this->testAction('/uploads/add', ['method' => 'GET']);
			$this->assertTrue(isset($this->headers['Location']));
		}

		public function testAddMustBePost() {
			$this->generate('Uploads');
			$this->_loginUser(1);
			$this->setExpectedException('MethodNotAllowedException');
			$this->testAction('/uploads/add', ['method' => 'GET']);
		}

		public function testAddMaxUploadsReached() {
			$_maxUploads = Configure::read(
				'Saito.Settings.upload_max_number_of_uploads'
			);

			$_currentUploads = 10;
			Configure::write(
				'Saito.Settings.upload_max_number_of_uploads',
				$_currentUploads
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
					->will($this->returnValue($_currentUploads));

			$Uploads->Upload->expects($this->never())
					->method('create');

			$result = $this->testAction('/uploads/add',
				['method' => 'post', 'return' => 'contents']);
			$result = json_decode($result);

			$_firstMessage = end($result->msg);
			$this->assertEquals($_firstMessage->type, 'error');

			Configure::write(
				'Saito.Settings.upload_max_number_of_uploads',
				$_maxUploads
			);
		}

		/**
		 * testIndex method
		 *
		 * @return void
		 */
		public function testIndexUserMustBeLoggedIn() {
			$this->setExpectedException('ForbiddenException');
			$this->testAction('/uploads/index', ['method' => 'GET']);
		}

		public function testIndexCallMustBeAjax() {
			$this->generate('Uploads');
			$this->_loginUser(1);
			$this->setExpectedException('BadRequestException');
			$this->testAction('/uploads/index', array('method' => 'get'));
		}

		public function testIndex() {
			$this->_setAjax();
			$this->_setJson();

			$this->generate('Uploads');
			$this->_loginUser(3);
			$result = $this->testAction( '/uploads/index', ['method' => 'GET', 'return' => 'contents']
			);
			$result = json_decode($result);
			$this->assertCount(1, $result);
			$this->assertEquals(
				$result[0]->name,
				'3_upload_test.png');
			$this->assertEquals(
				$result[0]->id,
				1);
		}

		public function testDeleteUserMustBeLoggedIn() {
			$this->setExpectedException('ForbiddenException');
			$this->testAction('/uploads/delete/1', ['method' => 'GET']);
			$this->assertTrue(isset($this->headers['Location']));
		}

		public function testDeleteCallMustBeAjax() {
			$this->generate('Uploads');
			$this->_loginUser(1);
			$this->setExpectedException('BadRequestException');
			$this->testAction('/uploads/delete/1', array('method' => 'get'));
		}

		public function testDeleteIdMustBeGiven() {
			$this->_setAjax();
			$this->generate('Uploads');
			$this->_loginUser(1);
			$this->setExpectedException('BadRequestException');
			$this->testAction('/uploads/delete', array('method' => 'get'));
		}

		public function testDeleteNotUsersImage() {
			$this->_setAjax();
			$this->generate('Uploads');
			$this->_loginUser(1);
			$this->setExpectedException('ForbiddenException');
			$this->testAction('/uploads/delete/1', array('method' => 'get'));
		}

		public function testDeleteImageDoesNotExist() {
			$this->_setAjax();
			$this->generate('Uploads');
			$this->_loginUser(1);
			$this->setExpectedException('ForbiddenException');
			$this->testAction('/uploads/delete/9999', array('method' => 'get'));
		}

		public function testDelete() {
			$this->_setAjax();
			$this->generate(
				'Uploads',
				array('models' => array('Upload' => array('delete')))
			);
			$this->controller->Upload->expects($this->once())
					->method('delete');
			$this->_loginUser(3);
			$this->testAction('/uploads/delete/1', array('method' => 'get'));
		}

	}
