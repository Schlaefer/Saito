<?php
App::uses('BookmarksController', 'Controller');
App::uses('SaitoControllerTestCase', 'Lib');

/**
 * BookmarksController Test Case
 *
 */
class BookmarksControllerTest extends SaitoControllerTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
			'app.bookmark',
			'app.category',
			'app.entry',
			'app.setting',
			'app.user',
			'app.user_online',
	);

	public function testIndexNotAllowed() {
		$this->expectException('MethodNotAllowedException');
		$result = $this->testAction('/bookmarks/index');
	}

	public function testIndex() {
		$Bookmarks = $this->generate('Bookmarks');
		$this->_loginUser(3);
		$result = $this->testAction('/bookmarks/index', array(
				'return' => 'view'
		));
		$this->assertContains('bookmarks/delete/1', $result);
		$this->assertContains('bookmarks/delete/2', $result);
		$this->assertNotContains('bookmarks/delete/3', $result);

		$this->assertContains('bookmarks/edit/1', $result);
		$this->assertContains('bookmarks/edit/2', $result);
		$this->assertNotContains('bookmarks/edit/3', $result);

		// check that output is sanatized
		$this->assertContains('&lt; Comment 2', $result);
	}

	public function testAddNoAjax() {
		// not ajax
		$this->expectException('BadRequestException');
		$result = $this->testAction('/bookmarks/add');
	}

	public function testAddSuccess() {
		$_ENV['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';

		$Bookmarks = $this->generate('Bookmarks', array(
				'models' => array(
						'Bookmark' => array(
								'save'
						)
				)
		));
		$data = array(
					'user_id'	 => 3,
					'entry_id' => 1,
			);
		$Bookmarks->Bookmark->expects($this->once())
				->method('save')
				->with($data);
		$this->_loginUser(3);
		$result		 = $this->testAction('/bookmarks/add',
				array(
				'method' => 'post', 'data'	 => array('id' => 1)
				));


		unset($_ENV['HTTP_X_REQUESTED_WITH']);
	}

	public function testEditNotLoggedIn() {
		$this->expectException('MethodNotAllowedException');
		$result = $this->testAction('/bookmarks/edit/1');
	}

	public function testEditNotUsersBookmark() {
		$Bookmarks = $this->generate('Bookmarks');
		$this->_loginUser(1);
		$this->expectException('MethodNotAllowedException');
		$result = $this->testAction('/bookmarks/edit/1');
	}

	public function testEdit() {
		$Bookmarks = $this->generate('Bookmarks', array(
				'models' => array(
						'Bookmark' => array(
								'save'
						)
				)
		));
		$this->_loginUser(3);

		$data = array(
				'Bookmark' => array(
						'comment'	 => 'test foo'
				)
		);

		$this->controller->Bookmark->expects($this->once())
				->method('save')
				->with($data);

		$result		 = $this->testAction('/bookmarks/edit/1',
				array(
				'method' => 'post', 'data'	 => $data
				));
		}

		public function testDeleteNotLoggedIn() {
			$this->expectException('MethodNotAllowedException');
			$result = $this->testAction('/bookmarks/delete/1');
		}

		public function testDeleteNotUsersBookmark() {
			$Bookmarks = $this->generate('Bookmarks');
			$this->_loginUser(1);
			$this->expectException('MethodNotAllowedException');
			$result = $this->testAction('/bookmarks/delete/1');
		}

		public function testDelete() {
			$Bookmarks = $this->generate('Bookmarks', array(
					'models' => array(
							'Bookmark' => array(
									'delete'
							)
					)
			));
			$this->controller->Bookmark->expects($this->once())
					->method('delete');
			$this->_loginUser(3);
			$result = $this->testAction('/bookmarks/delete/1');
		}

}
