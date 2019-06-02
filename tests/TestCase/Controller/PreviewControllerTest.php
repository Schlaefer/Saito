<?php

namespace App\Test\TestCase\Controller;

use Cake\Http\Exception\UnauthorizedException;
use Cake\ORM\TableRegistry;
use Saito\Test\IntegrationTestCase;

class PreviewControllerTest extends IntegrationTestCase
{

    /**
     * @var table for the controller
     */
    public $Table;

    public $fixtures = [
        'plugin.Bookmarks.Bookmark',
        'app.Category',
        'app.Entry',
        'app.Setting',
        'app.Smiley',
        'app.SmileyCode',
        'app.User',
        'app.UserBlock',
        'app.UserIgnore',
        'app.UserOnline',
        'app.UserRead'
    ];

    public function setUp()
    {
        parent::setUp();
        $this->Table = TableRegistry::get('Entries');
    }

    public function tearDown()
    {
        parent::tearDown();
        unset($this->Table);
    }

    public function testPreviewFailureNoAuthorization()
    {
        $this->expectException(UnauthorizedException::class);

        $this->get('preview/preview');
    }

    public function testPreviewSuccessNewThread()
    {
        $userId = 3;
        $this->loginJwt($userId);

        $data = [
            'category_id' => 2,
            'subject' => 'foo',
            'text' => 'bar',
        ];

        $this->post('preview/preview', $data);

        $this->assertResponseCode(200);
        $response = json_decode($this->_response->getBody(), true);

        $this->assertEquals(999999999999, $response['id']);
        $this->assertEquals(999999999999, $response['attributes']['id']);
        $this->assertEquals(2, $response['attributes']['category_id']);
        $this->assertEquals('foo', $response['attributes']['subject']);
        $this->assertEquals('bar', $response['attributes']['text']);
        $this->assertNotEmpty($response['attributes']['html']);
    }

    public function testPreviewSuccessNewAnswer()
    {
        $userId = 3;
        $this->loginJwt($userId);

        $data = [
            'pid' => 4,
            'category_id' => 99999,
            'text' => 'bar',
        ];

        $this->post('preview/preview', $data);

        $this->assertResponseCode(200);
        $response = json_decode($this->_response->getBody(), true);

        $this->assertEquals(999999999999, $response['id']);
        $this->assertEquals(999999999999, $response['attributes']['id']);
        $this->assertEquals(4, $response['attributes']['category_id']);
        $this->assertEquals('Second Thread First_Subject', $response['attributes']['subject']);
        $this->assertEquals('bar', $response['attributes']['text']);
        $this->assertNotEmpty($response['attributes']['html']);
    }

    public function testPreviewFailureNoCategory()
    {
        $userId = 3;
        $this->loginJwt($userId);

        $data = [
            'subject' => 'foo',
            'text' => 'bar',
        ];

        $this->post('preview/preview', $data);

        $this->assertResponseCode(400);
        $response = json_decode($this->_response->getBody(), true);

        $this->assertNotEmpty($response['errors'][0]['title']);
        $this->assertEquals('#category-id', $response['errors'][0]['meta']['field']);
    }

    public function testPreviewFailureNoSubjectOnRoot()
    {
        $userId = 3;
        $this->loginJwt($userId);

        $data = [
            'category_id' => 2,
            'text' => 'bar',
        ];

        $this->post('preview/preview', $data);

        $this->assertResponseCode(400);
        $response = json_decode($this->_response->getBody(), true);

        $this->assertNotEmpty($response['errors'][0]['title']);
        $this->assertEquals('#subject', $response['errors'][0]['meta']['field']);
    }
}
