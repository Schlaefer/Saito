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
//		'app.entry',
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
							'models' => array(
									'Ecach' => array(
											'deleteAll'
									),
							)
					)
			);
			$this->_loginUser(1);
			$Tools->Ecach->expects($this->once())
					->method('deleteAll')
					->with(array('true = true'));
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

