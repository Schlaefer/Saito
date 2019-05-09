<?php

	App::uses('ApiControllerTestCase', 'Api.Lib');

/**
 * ApiEntriesController Test Case
 *
 */
	class ApiEntriesControllerTest extends ApiControllerTestCase {

		protected $_apiRoot = 'api/v1/';

/**
 * Fixtures
 *
 * @var array
 */
		public $fixtures = array(
			'plugin.api.entry',
			'plugin.api.category',
			'plugin.api.user',
			'plugin.api.user_block',
			'plugin.api.user_ignore',
			'plugin.api.user_online',
			'plugin.api.user_read',
			'plugin.api.bookmark',
			'plugin.api.esnotification',
			'plugin.api.esevent',
			'plugin.api.upload',
			'plugin.api.setting',
			'plugin.api.smiley',
			'plugin.api.smiley_code'
		);

/**
 * testThreads method
 *
 * @return void
 */
		public function testThreads() {
			$this->generate('Api.ApiEntries');

			$this->_loginUser(1);

			$data = ['limit' => 2, 'offset' => 1, 'order' => 'answer'];
			$result = $this->testAction(
				$this->_apiRoot . 'threads.json',
				['return' => 'contents', 'method' => 'GET', 'data' => $data]
			);
			$expected = json_decode('
			[
				{
					"id": 4,
					"subject": "Second Thread First_Subject",
					"is_nt": true,
					"is_pinned": false,
					"time": "2000-01-01T10:00:00+00:00",
					"last_answer": "2000-01-04T20:02:00+00:00",
					"user_id": 1,
					"user_name": "Alice",
					"category_id": 4,
					"category_name": "Offtopic"
				},
				{
					"id": 6,
					"subject": "Third Thread First_Subject",
					"is_nt": true,
					"is_pinned": false,
					"time": "2000-01-01T11:00:00+00:00",
					"last_answer": "2000-01-01T11:00:00+00:00",
					"user_id": 1,
					"user_name": "Alice",
					"category_id": 1,
					"category_name": "Admin"
				}
			]');
			$this->assertEqual(json_decode($result), $expected);
		}

/**
 * Tests that anon doesn't see user and admin categories
 */
		public function testThreadsNoAdminAnon() {
			$data = ['limit' => 3];
			$result = $this->testAction(
				$this->_apiRoot . 'threads.json',
				['return' => 'contents', 'method' => 'GET', 'data' => $data]
			);
			$result = json_decode($result, true);
			$this->assertEqual(count($result), 3);
			$this->assertEqual($result[0]['id'], 1);
			$this->assertEqual($result[1]['id'], 10);
		}

/**
 * Tests that user doesn't see admin category
 */
		public function testThreadsNoAdminUser() {
			$this->generate('ApiEntries');
			$this->_loginUser(3);
			$data = ['limit' => 2, 'offset' => 1, 'order' => 'answer'];
			$result = $this->testAction(
				$this->_apiRoot . 'threads.json',
				['return' => 'contents', 'method' => 'GET', 'data' => $data]
			);
			$result = json_decode($result, true);
			$this->assertEqual($result[0]['id'], 4);
			$this->assertEqual($result[1]['id'], 10);
		}

		public function testThreadsDisallowedRequestTypes() {
			$this->_checkDisallowedRequestType(
				['POST', 'PUT', 'DELETE'],
					$this->_apiRoot . 'threads'
			);
		}

		public function testEntriesItemPostEmptySubject() {
			$this->generate('Api.ApiEntries');
			$this->_loginUser(3);
			$this->setExpectedException('Saito\Api\ApiValidationError',
				'Subject must not be empty.');
			$this->testAction(
				$this->_apiRoot . 'entries.json',
				[
					'method' => 'POST',
					'data' => [
						'subject' => '',
						'parent_id' => 0,
						'category_id' => 2
					]
				]
			);
		}

		public function testEntriesItemPostSuccess() {
			$this->generate('Api.ApiEntries');
			$this->_loginUser(3);
			$result = $this->testAction(
				$this->_apiRoot . 'entries.json',
				[
					'return' => 'view',
					'method' => 'POST',
					'data' => [
						'subject' => 'subject',
						'parent_id' => 0,
						'category_id' => 2
					]
				]
			);
			$statusCode = $this->controller->response->statusCode();
			$this->assertEquals(200, $statusCode);
		}

		public function testEntriesItemPutOnlyAuthenticatedUsers() {
			$this->generate('ApiEntries', ['methods' => 'entriesItemPut']);
			$this->testAction($this->_apiRoot . 'entries/1', ['method' => 'PUT']);
			$this->assertRedirectedTo('login');
		}

		public function testEntriesItemEntryIdMustBeProvided() {
			$this->setExpectedException('BadRequestException', 'Missing entry id.');
			$this->testAction($this->_apiRoot . 'entries/', ['method' => 'PUT']);
		}

		public function testEntriesItemPutEntryMustExist() {
			$this->setExpectedException('NotFoundException', 'Entry with id `999` not found.');
			$this->testAction($this->_apiRoot . 'entries/999', ['method' => 'PUT']);
		}

		public function testEntriesItemPutForbiddenTime() {
			$this->generate('Api.ApiEntries');
			$this->_loginUser(3);
			$this->setExpectedException('ForbiddenException', 'The editing time ran out.');
			$this->testAction(
				$this->_apiRoot . 'entries/1',
				[
					'method' => 'PUT',
					'data' => [
						'subject' => 'foo',
						'text' => 'bar'
					]
				]
			);
		}

		public function testEntriesItemPutForbiddenUser() {
			$ApiEntries = $this->generate('Api.ApiEntries');
			$this->_loginUser(3);
			$this->setExpectedException('ForbiddenException', 'The user `Ulysses` is not allowed to edit.');
			$id = 2;
			$ApiEntries->Entry->save(['id' => $id, 'time' => bdate()]);
			$this->testAction(
				$this->_apiRoot . 'entries/' . $id,
				[
					'method' => 'PUT',
					'data' => [
						'subject' => 'foo',
						'text' => 'bar'
					]
				]
			);
		}

		public function testEntriesItemPutForbiddenJustTrue() {
			$ApiEntries = $this->generate(
				'Api.ApiEntries',
				['models' => ['Entry' => ['get']]]
			);

			$entry = [
				'Entry' => [
					'id' => 1,
					'locked' => true,
					'time' => bDate(time() + 9999),
					'user_id' => 3
				]
			];

			$ApiEntries->Entry->expects($this->once())
					->method('get')
					->will($this->returnValue($entry));
			$this->_loginUser(3);
			$this->setExpectedException('ForbiddenException', 'Editing is forbidden for unknown reason.');
			$this->testAction(
				$this->_apiRoot . 'entries/1',
				[
					'method' => 'PUT',
					'data' => [
						'subject' => 'foo',
						'text' => 'bar'
					]
				]
			);
		}

		public function testEntriesItemPutSuccess() {
			$this->generate('Api.ApiEntries');
			$this->_loginUser(1);
			$id = 1;
			$result = $this->testAction(
				$this->_apiRoot . 'entries/' . $id . '.json',
				[
					'method' => 'PUT',
					'data' => [
						'subject' => 'foo',
						'text' => 'bar'
					],
					'return' => 'contents'
				]
			);
			$expected = json_decode('
			{
				"id": 1,
				"parent_id": 0,
				"thread_id": 1,
				"subject": "foo",
				"is_nt": false,
				"is_pinned": false,
				"is_locked": false,
				"time": "2000-01-01T20:00:00+00:00",
				"last_answer": "2000-01-04T20:02:00+00:00",
				"text": "bar",
				"html": "bar",
				"user_id": 3,
				"user_name": "Ulysses",
				"edit_name": "Alice",
				"category_id": 2,
				"category_name": "Ontopic"
			}
			', true);

			$result = json_decode($result, true);
			$_editTime = $result['edit_time'];
			unset($result['edit_time']);

			$this->assertGreaterThan(time() - 5, strtotime($_editTime));

			$this->assertEqual($result, $expected);
		}

		public function testEntriesItemPutErrorOnUpdate() {
			$ApiEntries = $this->generate(
				'Api.ApiEntries',
				['models' => ['Entry' => ['update']]]
			);
			$this->_loginUser(1);
			$this->setExpectedException('BadRequestException', 'Tried to save entry but failed for unknown reason.');
			$id = 1;
			$ApiEntries->Entry->save(['id' => $id, 'time' => bdate()]);
			$ApiEntries->Entry->expects($this->once())
				->method('update')
				->will($this->returnValue(false));
			$this->testAction(
				$this->_apiRoot . 'entries/' . $id,
				[
					'method' => 'PUT',
					'data' => [
						'subject' => 'foo',
						'text' => 'bar'
					]
				]
			);
		}

		public function testEntriesItemDisallowedRequestTypes() {
			$this->_checkDisallowedRequestType(
				['GET', 'POST', 'DELETE'],
					$this->_apiRoot . 'entries/1'
			);
		}

		public function testThreadsItemGetThreadNotFound() {
			$this->setExpectedException('NotFoundException', 'Thread with id `2` not found.');
			$this->testAction($this->_apiRoot . 'threads/2.json', ['method' => 'GET']);
		}

		public function testThreadsItemGet() {
			$this->generate('ApiEntries');
			$this->_loginUser(3);
			$result = $this->testAction(
				$this->_apiRoot . 'threads/1.json',
				['method' => 'GET', 'return' => 'contents']
			);
			$json = <<< EOF
				[
					{
						"id": 3,
						"parent_id": 2,
						"thread_id": 1,
						"subject": "Third_Subject",
						"is_nt": false,
						"time": "2000-01-01T20:02:00+00:00",
						"last_answer": "2000-01-01T20:02:00+00:00",
						"text": "< Third_Text",
						"html": "&lt; Third_Text",
						"user_id": 3,
						"user_name": "Ulysses",
						"edit_name": "Ulysses",
						"edit_time": "2000-01-01T20:04:00+00:00",
						"category_id": 2,
						"category_name": "Ontopic",
						"is_locked": false,
						"is_pinned": false
				}
			]
EOF;
			$expected = json_decode($json, true);
			$result = json_decode($result, true);
			$this->assertCount(6, $result);
			$this->assertEqual($result[2], $expected[0]);
		}

		public function testThreadsItemGetNotLoggedIn() {
			$result = $this->testAction(
				$this->_apiRoot . 'threads/10.json',
				['method' => 'GET', 'return' => 'contents']
			);
			$result = json_decode($result, true);
			$this->assertFalse(
				isset($result[0]['is_locked']),
				'Property `is_locked` should not be visible to anon user.'
			);
		}

/**
 * Tests that anon can't see user category
 */
		public function testThreadsItemGetNotLoggedInCategory() {
			$this->setExpectedException('NotFoundException', 'Thread with id `4` not found.');
			$this->testAction(
				$this->_apiRoot . 'threads/4.json',
				['method' => 'GET', 'return' => 'contents']
			);
		}

/**
 * Tests that user can see user category
 */
		public function testThreadsItemGetLoggedInCategory() {
			$this->generate('ApiEntries');
			$this->_loginUser(3);
			$result = $this->testAction(
				$this->_apiRoot . 'threads/4.json',
				['method' => 'GET', 'return' => 'contents']
			);
			$result = json_decode($result, true);
			$this->assertEqual($result[0]['id'], 4);
		}

/**
 * Tests that anon can't see admin category
 */
		public function testThreadsItemGetNotAdminAnonCategory() {
			$this->setExpectedException('NotFoundException', 'Thread with id `6` not found.');
			$this->testAction(
				$this->_apiRoot . 'threads/6.json',
				['method' => 'GET', 'return' => 'contents']
			);
		}

/**
 * Tests that user can't see admin category
 */
		public function testThreadsItemGetNotAdminUserCategory() {
			$this->generate('ApiEntries');
			$this->_loginUser(3);

			$this->setExpectedException('NotFoundException', 'Thread with id `6` not found.');
			$this->testAction(
				$this->_apiRoot . 'threads/6.json',
				['method' => 'GET', 'return' => 'contents']
			);
		}

/**
 * Tests that admin can see admin category.
 */
		public function testThreadsItemGetAdminCategory() {
			$this->generate('ApiEntries');
			$this->_loginUser(1);
			$result = $this->testAction(
				$this->_apiRoot . 'threads/6.json',
				['method' => 'GET', 'return' => 'contents']
			);
			$result = json_decode($result, true);
			$this->assertEqual($result[0]['id'], 6);
		}

		public function testThreadsItemDisallowedRequestTypes() {
			$this->_checkDisallowedRequestType(
				['PUT', 'POST', 'DELETE'],
					$this->_apiRoot . 'threads/1'
			);
		}

	}
