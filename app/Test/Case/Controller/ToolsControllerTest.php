<?php

	App::uses('ToolsController', 'Controller');
	App::uses('SaitoControllerTestCase', 'Lib');

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
				'app.ecach',
				'app.setting',
				'app.user',
				'app.user_online',
//		'app.bookmark',
				'app.entry',
//		'app.category',
//		'app.esevent',
//		'app.esnotification',
//		'app.upload'
		);

		/**
		 * testAdminEmptyCaches method
		 *
		 * @return void
		 */
		public function testAdminEmptyCachesNonAdmin() {
			$this->expectException('ForbiddenException');
			$this->testAction('admin/tools/emptyCaches');
		}

		public function testAdminEmptyCachesUser() {
			$Tools = $this->generate('Tools');
			$this->_loginUser(3);
			$this->expectException('ForbiddenException');
			$this->testAction('admin/tools/emptyCaches');
		}

		public function testAdminEmptyCaches() {
			$Tools = $this->generate('Tools',
					array(
							'components' => array(
									'CacheSupport' => array(
											'clearAll'
									),
							)
					)
			);
			$this->_loginUser(1);
			$Tools->CacheSupport->expects($this->once())
					->method('clearAll');
			$this->testAction('admin/tools/emptyCaches');
		}

		/**
		 * testClearCache method
		 *
		 * @return void
		 */
		public function testClearCache() {

		}

	}

