<?php

declare(strict_types=1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace App\Test\TestCase\Controller;

use Authentication\Authenticator\UnauthenticatedException;
use Cake\ORM\TableRegistry;
use Cake\Utility\Hash;
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
        'app.UserRead',
    ];

    public function setUp(): void
    {
        parent::setUp();
        $this->Table = TableRegistry::get('Entries');
    }

    public function tearDown(): void
    {
        parent::tearDown();
        unset($this->Table);
    }

    public function testPreviewFailureNoAuthorization()
    {
        $this->expectException(UnauthenticatedException::class);

        $this->get('/api/v2/preview/preview');
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

        $this->post('/api/v2/preview/preview', $data);

        $this->assertResponseCode(200);
        $response = json_decode((string)$this->_response->getBody(), true);

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
            'category_id' => 2,
            'text' => 'bar',
        ];

        $this->post('/api/v2/preview/preview', $data);

        $this->assertResponseCode(200);
        $response = json_decode((string)$this->_response->getBody(), true);

        $this->assertEquals(999999999999, $response['id']);
        $this->assertEquals(999999999999, $response['attributes']['id']);
        $this->assertEquals(2, $response['attributes']['category_id']);
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

        $this->post('/api/v2/preview/preview', $data);

        $this->assertResponseCode(200);
        $response = json_decode((string)$this->_response->getBody(), true);

        $this->assertArrayHasKey('errors', $response);

        $pointers = array_flip(Hash::extract($response, 'errors.{n}.source.pointer'));
        $this->assertArrayHasKey('/data/attributes/category_id', $pointers);
    }

    public function testPreviewFailureNoSubjectOnRoot()
    {
        $userId = 3;
        $this->loginJwt($userId);

        $data = [
            'category_id' => 2,
            'text' => 'bar',
        ];

        $this->post('/api/v2/preview/preview', $data);

        $this->assertResponseCode(200);
        $response = json_decode((string)$this->_response->getBody(), true);

        $this->assertArrayHasKey('errors', $response);

        $pointers = array_flip(Hash::extract($response, 'errors.{n}.source.pointer'));
        $this->assertArrayHasKey('/data/attributes/subject', $pointers);
    }
}
