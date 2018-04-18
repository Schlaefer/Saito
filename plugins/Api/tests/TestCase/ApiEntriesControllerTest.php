<?php

namespace Api\Test;

use Api\Lib\ApiIntegrationTestCase;
use Cake\ORM\TableRegistry;

/**
 * ApiEntriesController Test Case
 *
 */
class ApiEntriesControllerTest extends ApiIntegrationTestCase
{

    protected $_apiRoot = 'api/v1/';

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'app.category',
        'app.entry',
        'app.esevent',
        'app.esnotification',
        'app.setting',
        'app.smiley',
        'app.smiley_code',
        'app.upload',
        'app.user',
        'app.user_block',
        'app.user_ignore',
        'app.user_online',
        'app.user_read',
        'plugin.bookmarks.bookmark'
    ];

    /**
     * testThreads method
     *
     * @return void
     */
    public function testThreads()
    {
        $this->_loginUser(1);

        $data = ['limit' => 2, 'offset' => 1, 'order' => 'answer'];
        $url = $this->_apiRoot . 'threads.json?' . http_build_query($data);
        $this->get($url);
        $expected = json_decode(
            '
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
					"id": 13,
					"subject": "Subject 13",
					"is_nt": false,
					"is_pinned": false,
					"time": "2000-01-01T12:00:00+00:00",
					"last_answer": "2000-01-01T12:00:00+00:00",
					"user_id": 1,
					"user_name": "Alice",
					"category_id": 2,
					"category_name": "Ontopic"
				}
			]',
            true
        );
        $result = json_decode((string)$this->_response->getBody(), true);
        $this->assertEquals($expected, $result);
    }

    /**
     * Tests that anon doesn't see user and admin categories
     */
    public function testThreadsNoAdminAnon()
    {
        $this->get($this->_apiRoot . 'threads.json?limit=3');
        $result = json_decode((string)$this->_response->getBody(), true);
        $this->assertEquals(3, count($result));
        $this->assertEquals(1, $result[0]['id']);
        $this->assertEquals(13, $result[1]['id']);
    }

    /**
     * Tests that user doesn't see admin category
     */
    public function testThreadsNoAdminUser()
    {
        $this->_loginUser(3);
        $data = ['limit' => 2, 'offset' => 1, 'order' => 'answer'];
        $this->get(
            $this->_apiRoot . 'threads.json?' . http_build_query($data)
        );
        $result = json_decode((string)$this->_response->getBody(), true);
        $this->assertEquals(4, $result[0]['id']);
        $this->assertEquals(13, $result[1]['id']);
    }

    public function testThreadsDisallowedRequestTypes()
    {
        $this->_checkDisallowedRequestType(
            ['POST', 'PUT', 'DELETE'],
            $this->_apiRoot . 'threads.json'
        );
    }

    public function testEntriesItemPostEmptySubject()
    {
        $this->_loginUser(3);
        $this->expectException(
            '\Api\Error\Exception\ApiValidationException',
            'Subject must not be empty.'
        );
        $this->post(
            $this->_apiRoot . 'entries.json',
            ['subject' => '', 'parent_id' => 0, 'category_id' => 3]
        );
    }

    /**
     * cagtegory 1 is not allowed to user
     */
    public function testEntriesItemPostNotAllowed()
    {
        $this->_loginUser(3);
        $this->expectException('\Cake\Http\Exception\BadRequestException');
        $this->post(
            $this->_apiRoot . 'entries.json',
            ['subject' => 'subject', 'parent_id' => 0, 'category_id' => 1]
        );
        $this->assertResponseCode(200);
    }

    public function testEntriesItemPostNoCategoryProvided()
    {
        $this->_loginUser(3);
        $this->expectException(
            '\Api\Error\Exception\ApiValidationException',
            'category_id'
        );
        $this->post(
            $this->_apiRoot . 'entries.json',
            ['subject' => 'subject', 'parent_id' => 0]
        );
    }

    public function testEntriesItemPostCategoryValid()
    {
        $this->_loginUser(3);
        $this->expectException(
            '\Api\Error\Exception\ApiValidationException',
            'category_id'
        );
        $this->post(
            $this->_apiRoot . 'entries.json',
            ['subject' => 'subject', 'parent_id' => 0, 'category_id' => 9999]
        );
    }

    public function testEntriesItemPostSuccess()
    {
        $this->_loginUser(3);
        $this->post(
            $this->_apiRoot . 'entries.json',
            ['subject' => 'subject', 'parent_id' => 0, 'category_id' => 2]
        );
        $this->assertResponseCode(200);
    }

    public function testEntriesItemPutOnlyAuthenticatedUsers()
    {
        $this->put($this->_apiRoot . 'entries/1');
        $this->assertRedirect('/login');
    }

    public function testEntriesItemEntryIdMustBeProvided()
    {
        $this->_loginUser(1);
        $this->expectException(
            '\Cake\Http\Exception\BadRequestException',
            'Missing entry id.'
        );
        $this->put($this->_apiRoot . 'entries/');
    }

    public function testEntriesItemPutEntryMustExist()
    {
        $this->_loginUser(1);
        $this->expectException(
            '\Cake\Http\Exception\NotFoundException',
            'Entry with id `999` not found.'
        );
        $this->put($this->_apiRoot . 'entries/999');
    }

    public function testEntriesItemPutForbiddenTime()
    {
        $this->_loginUser(3);
        $this->expectException(
            '\Cake\Http\Exception\ForbiddenException',
            'The editing time ran out.'
        );
        $this->put(
            $this->_apiRoot . 'entries/1',
            ['subject' => 'foo', 'text' => 'bar']
        );
    }

    public function testEntriesItemPutForbiddenUser()
    {
        $postingId = 2;
        $Postings = TableRegistry::get('Entries');
        $Postings->updateAll(
            ['time' => bDate(time())],
            ['id' => $postingId]
        );

        $this->_loginUser(3);
        $this->expectException(
            '\Cake\Http\Exception\ForbiddenException',
            'The user `Ulysses` is not allowed to edit.'
        );

        $this->put(
            $this->_apiRoot . 'entries/' . $postingId,
            ['subject' => 'foo', 'text' => 'bar']
        );
    }

    public function testEntriesItemPutForbiddenJustTrue()
    {
        $postingId = 1;
        $Postings = TableRegistry::get('Entries');
        $Postings->updateAll(
            ['locked' => true, 'time' => bDate(time() + 9999), 'user_id' => 3],
            ['id' => $postingId]
        );

        $this->_loginUser(3);
        $this->expectException(
            '\Cake\Http\Exception\ForbiddenException',
            'Editing is forbidden for unknown reason.'
        );
        $this->put(
            $this->_apiRoot . "entries/{$postingId}",
            ['subject' => 'foo', 'text' => 'bar']
        );
    }

    public function testEntriesItemPutSuccess()
    {
        $this->_loginUser(1);
        $id = 1;
        $this->put(
            $this->_apiRoot . 'entries/' . $id . '.json',
            [
                'subject' => 'foo',
                'text' => 'bar'
            ]
        );
        $expected = json_decode(
            '
			{
				"id": 1,
				"parent_id": 0,
				"thread_id": 1,
				"subject": "foo",
				"is_nt": false,
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
			',
            true
        );
        $result = json_decode((string)$this->_response->getBody(), true);

        $editTime = $result['edit_time'];
        unset($result['edit_time']);
        // 60 ticks seems unusually high, but the DB-result checkes out
        $this->assertWithinRange(time(), strtotime($editTime), 60);

        $this->assertEquals($expected, $result);
    }

    public function testEntriesItemPutErrorOnUpdate()
    {
        $postingId = 4;
        $Postings = $this->getMockForTable('Entries', ['update']);
        $errorPosting = $Postings->newEntity(['user_id' => null]);
        $Postings->expects($this->once())
            ->method('update')
            ->will($this->returnValue($errorPosting));

        $Postings->updateAll(['time' => bDate()], ['id' => $postingId]);

        $this->_loginUser(1);
        $this->expectException(
            '\Cake\Http\Exception\BadRequestException',
            'Tried to save entry but failed for unknown reason.'
        );
        $this->put(
            $this->_apiRoot . 'entries/' . $postingId . 'json',
            ['subject' => 'foo', 'text' => 'bar']
        );
    }

    public function testEntriesItemDisallowedRequestTypes()
    {
        $this->_checkDisallowedRequestType(
            ['GET', 'POST', 'DELETE'],
            $this->_apiRoot . 'entries/1'
        );
    }

    public function testThreadsItemGetThreadNotFound()
    {
        $this->expectException(
            '\Cake\Http\Exception\NotFoundException',
            'Thread with id `2` not found.'
        );
        $this->get($this->_apiRoot . 'threads/2.json');
    }

    public function testThreadsItemGet()
    {
        $this->_loginUser(3);
        $this->get($this->_apiRoot . 'threads/1.json');
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
						"is_locked": false
				}
			]
EOF;
        $expected = json_decode($json, true);
        $result = json_decode((string)$this->_response->getBody(), true);
        $this->assertCount(6, $result);
        $this->assertEquals($expected[0], $result[2]);
    }

    public function testThreadsItemGetNotLoggedIn()
    {
        $this->get($this->_apiRoot . 'threads/10.json');
        $result = json_decode((string)$this->_response->getBody(), true);
        $this->assertFalse(
            isset($result[0]['is_locked']),
            'Property `is_locked` should not be visible to anon user.'
        );
    }

    /**
     * Tests that anon can't see user category
     */
    public function testThreadsItemGetNotLoggedInCategory()
    {
        $this->expectException(
            '\Cake\Http\Exception\NotFoundException',
            'Thread with id `4` not found.'
        );
        $this->get($this->_apiRoot . 'threads/4.json');
    }

    /**
     * Tests that user can see user category
     */
    public function testThreadsItemGetLoggedInCategory()
    {
        $this->_loginUser(3);
        $this->get($this->_apiRoot . 'threads/4.json');
        $result = json_decode((string)$this->_response->getBody(), true);
        $this->assertEquals(4, $result[0]['id']);
    }

    /**
     * Tests that anon can't see admin category
     */
    public function testThreadsItemGetNotAdminAnonCategory()
    {
        $this->expectException(
            '\Cake\Http\Exception\NotFoundException',
            'Thread with id `6` not found.'
        );
        $this->get($this->_apiRoot . 'threads/6.json');
    }

    /**
     * Tests that user can't see admin category
     */
    public function testThreadsItemGetNotAdminUserCategory()
    {
        $this->_loginUser(3);

        $this->expectException(
            '\Cake\Http\Exception\NotFoundException',
            'Thread with id `6` not found.'
        );
        $this->get($this->_apiRoot . 'threads/6.json');
    }

    /**
     * Tests that admin can see admin category.
     */
    public function testThreadsItemGetAdminCategory()
    {
        $this->_loginUser(1);
        $this->get($this->_apiRoot . 'threads/6.json');
        $result = json_decode((string)$this->_response->getBody(), true);
        $this->assertEquals(6, $result[0]['id']);
    }

    public function testThreadsItemDisallowedRequestTypes()
    {
        $this->_checkDisallowedRequestType(
            ['PUT', 'POST', 'DELETE'],
            $this->_apiRoot . 'threads/1'
        );
    }
}
