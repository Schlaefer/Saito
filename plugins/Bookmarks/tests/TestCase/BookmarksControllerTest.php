<?php

	namespace Bookmarks\Test\TestCase\Controller;

	use Saito\Test\IntegrationTestCase;

	class BookmarksControllerTest extends IntegrationTestCase {

		public $fixtures = [
			'app.category',
			'app.entry',
			'app.setting',
			'app.smiley',
			'app.smiley_code',
			'app.user',
			'app.user_ignore',
			'app.user_online',
			'app.user_read',
			'plugin.bookmarks.bookmark'
		];

		public function testIndexNotAllowed() {
			$this->get('/bookmarks/index');
			$this->assertRedirect('/login');
		}

		public function testIndex() {
			$this->_loginUser(3);
			$this->get('/bookmarks/index');

			$this->assertResponseContains('bookmarks/edit/1');
			$this->assertResponseContains('bookmarks/edit/2');
			$this->assertResponseNotContains('bookmarks/edit/3');

			// check that output is sanitized
			$this->assertResponseContains('&lt; Comment 2');
		}

		public function testAddNoAjax() {
			$this->_loginUser(3);
            $this->setExpectedException('Cake\Network\Exception\BadRequestException');
			$this->get('/bookmarks/add');
		}

		public function testAddSuccess() {
			$this->markTestIncomplete('@todo 3.0');
			$this->_setAjax();

			$Bookmarks = $this->generate('Bookmarks', [
				'models' => ['Bookmark' => ['save']]]);
			$data = ['user_id' => 3, 'entry_id' => 1];
			$Bookmarks->Bookmark->expects($this->once())
				->method('save')
				->with($data);
			$this->_loginUser(3);
			$this->testAction('/bookmarks/add',
				['return' => 'view', 'data' => ['id' => 1]]);
		}

		public function testEditNotLoggedIn() {
			$this->markTestIncomplete('@todo 3.0');
			$this->setExpectedException('MethodNotAllowedException');
			$this->testAction('/bookmarks/edit/1');
		}

		public function testEditNotUsersBookmark() {
			$this->markTestIncomplete('@todo 3.0');
			$this->generate('Bookmarks');
			$this->_loginUser(1);
			$this->setExpectedException('Saito\Exception\SaitoForbiddenException');
			$this->testAction('/bookmarks/edit/1');
		}

		public function testEditRead() {
			$this->markTestIncomplete('@todo 3.0');
			$Bookmarks = $this->generate('Bookmarks');
			$this->_loginUser(3);

			$result = $this->testAction('/bookmarks/edit/5',
				['method' => 'GET', 'return' => 'view']);

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
			$this->markTestIncomplete('@todo 3.0');
			$this->generate('Bookmarks', ['models' => ['Bookmark' => ['save']]]);
			$this->_loginUser(3);

			$data = ['Bookmark' => ['comment' => 'test foo']];

			$expected = $data;
			$expected['Bookmark']['id'] = 1;
			$this->controller->Bookmark->expects($this->once())
				->method('save')
				->with($expected);

			$this->testAction('/bookmarks/edit/1',
				['method' => 'post', 'data' => $data]);
		}

		public function testDeleteNoAjax() {
			$this->markTestIncomplete('@todo 3.0');
			$this->setExpectedException('BadRequestException');
			$this->testAction('/bookmarks/delete/1');
		}

		public function testDeleteNotUsersBookmark() {
			$this->markTestIncomplete('@todo 3.0');
			$this->_setAjax();
			$this->generate('Bookmarks');
			$this->_loginUser(1);

			$this->setExpectedException('Saito\Exception\SaitoForbiddenException');
			$this->testAction('/bookmarks/delete/1', ['method' => 'POST']);
		}

		public function testDelete() {
			$this->markTestIncomplete('@todo 3.0');
			$this->_setAjax();
			$this->generate('Bookmarks',
				['models' => ['Bookmark' => ['delete']]]);
			$this->_loginUser(3);

			$this->controller->Bookmark->expects($this->once())
				->method('delete');

			$this->testAction('/bookmarks/delete/1', ['method' => 'post']);
		}

	}
