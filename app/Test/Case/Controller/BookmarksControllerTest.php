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
		public $fixtures = [
			'app.bookmark',
			'app.category',
			'app.ecach',
			'app.entry',
			'app.setting',
			'app.smiley',
			'app.smiley_code',
			'app.user',
			'app.user_online',
			'app.user_read'
		];

		public function testIndexNotAllowed() {
			$this->setExpectedException('MethodNotAllowedException');
			$this->testAction('/bookmarks/index');
		}

		public function testIndex() {
			$this->generate('Bookmarks');
			$this->_loginUser(3);
			$result = $this->testAction('/bookmarks/index',
				['method' => 'GET', 'return' => 'view']);

			$this->assertContains('bookmarks/edit/1', $result);
			$this->assertContains('bookmarks/edit/2', $result);
			$this->assertNotContains('bookmarks/edit/3', $result);

			// check that output is sanitized
			$this->assertContains('&lt; Comment 2', $result);
		}

		public function testAddNoAjax() {
			$this->setExpectedException('BadRequestException');
			$this->testAction('/bookmarks/add');
		}

		public function testAddSuccess() {
			$this->_setAjax();

			$Bookmarks = $this->generate('Bookmarks', [
				'models' => [
					'Bookmark' => ['save']
				]]);
			$data = ['user_id' => 3, 'entry_id' => 1];
			$Bookmarks->Bookmark->expects($this->once())
					->method('save')
					->with($data);
			$this->_loginUser(3);
			$this->testAction('/bookmarks/add',
				['return' => 'view', 'data' => ['id' => 1]]);
		}

		public function testEditNotLoggedIn() {
			$this->setExpectedException('MethodNotAllowedException');
			$this->testAction('/bookmarks/edit/1');
		}

		public function testEditNotUsersBookmark() {
			$this->generate('Bookmarks');
			$this->_loginUser(1);
			$this->setExpectedException('MethodNotAllowedException');
			$this->testAction('/bookmarks/edit/1');
		}

		public function testEditRead() {
			$Bookmarks = $this->generate('Bookmarks');
			$this->_loginUser(3);
			$result = $this->testAction('/bookmarks/edit/5', ['method' => 'GET', 'return' => 'view']);
			$this->assertEquals($Bookmarks->request->data['Bookmark']['comment'],
					'<BookmarkComment');

			// special chars are escaped
			$this->assertContains('&lt;BookmarkComment', $result);
			$this->assertNotContains('<BookmarkComment', $result);
			$this->assertContains('&lt;Subject', $result);
			$this->assertNotContains('<Subject', $result);
			$this->assertContains('&lt;Text', $result);
			$this->assertNotContains('<Text', $result);
		}

		public function testEditSave() {
			$Bookmarks = $this->generate('Bookmarks',
				array(
					'models' => array(
						'Bookmark' => array(
							'save'
						)
					)
				));
			$this->_loginUser(3);

			$data = array(
				'Bookmark' => array(
					'comment' => 'test foo'
				)
			);

			$this->controller->Bookmark->expects($this->once())
					->method('save')
					->with($data);

			$result = $this->testAction('/bookmarks/edit/1',
				array(
					'method' => 'post',
					'data' => $data
				));
		}

		public function testDeleteNoAjax() {
			$this->setExpectedException('BadRequestException');
			$result = $this->testAction('/bookmarks/delete/1');
		}

		public function testDeleteNotUsersBookmark() {
			$_ENV['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';

			$Bookmarks = $this->generate('Bookmarks');
			$this->_loginUser(1);
			$this->setExpectedException('MethodNotAllowedException');
			$result = $this->testAction('/bookmarks/delete/1',
				array(
					'method' => 'post'
				)
			);

			unset($_ENV['HTTP_X_REQUESTED_WITH']);
		}

		public function testDelete() {
			$_ENV['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';

			$Bookmarks = $this->generate('Bookmarks',
				array(
					'models' => array(
						'Bookmark' => array(
							'delete'
						)
					)
				));
			$this->controller->Bookmark->expects($this->once())
					->method('delete');
			$this->_loginUser(3);
			$result = $this->testAction('/bookmarks/delete/1',
				array(
					'method' => 'post',
				)
			);

			unset($_ENV['HTTP_X_REQUESTED_WITH']);
		}

	}
