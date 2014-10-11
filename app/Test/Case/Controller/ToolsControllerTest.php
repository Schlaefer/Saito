<?php

	App::uses('ToolsController', 'Controller');
	App::uses('SaitoControllerTestCase', 'Lib/Test');

	/**
	 * ToolsController Test Case
	 *
	 */
	class ToolsControllerTest extends SaitoControllerTestCase {

		/**
		 * Fixtures
		 *
		 * @var array
		 */
		public $fixtures = array(
			'app.category',
			'app.ecach',
			'app.entry',
			'app.setting',
			'app.user',
			'app.user_ignore',
			'app.user_online',
			'app.user_read',
		);

		/**
		 * testAdminEmptyCaches method
		 *
		 * @return void
		 */
		public function testAdminEmptyCachesNonAdmin() {
			$this->setExpectedException('ForbiddenException');
			$this->testAction('admin/tools/emptyCaches');
		}

		public function testAdminEmptyCachesUser() {
			$Tools = $this->generate('Tools');
			$this->_loginUser(3);
			$this->setExpectedException('ForbiddenException');
			$this->testAction('admin/tools/emptyCaches');
		}

		public function testAdminEmptyCaches() {
			$Tools = $this->generate(
				'Tools',
				['components' => ['CacheSupport' => ['clear']]]
			);
			$this->_loginUser(1);
			$Tools->CacheSupport->expects($this->once())
					->method('clear');
			$this->testAction('admin/tools/emptyCaches');
		}

	}

