<?php

	class AdminsControllerTest extends \Saito\Test\ControllerTestCase {

		public $fixtures = [
			'app.category',
			'app.entry',
			'app.setting',
			'app.user',
			'app.user_block',
			'app.user_ignore',
			'app.user_online',
			'app.user_read',
		];


		public function testPhpInfoNotAllowedAnon() {
			$this->setExpectedException('ForbiddenException');
			$this->testAction('/admin/admins/phpinfo');
		}

		public function testPhpInfoNotAllowedUser() {
			$this->generate('Admins');
			$this->_loginUser(3);
			$this->setExpectedException('ForbiddenException');
			$this->testAction('/admin/admins/phpinfo');
		}

		public function testPhpInfo() {
			$this->generate('Admins');
			$this->_loginUser(1);
			$this->testAction('/admin/admins/phpinfo');
		}

	}
