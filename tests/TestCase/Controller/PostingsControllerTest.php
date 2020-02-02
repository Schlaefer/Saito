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
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Http\Exception\BadRequestException;
use Cake\ORM\TableRegistry;
use Cake\Utility\Hash;
use InvalidArgumentException;
use Saito\Exception\SaitoForbiddenException;
use Saito\Test\IntegrationTestCase;

class PostingsControllerTest extends IntegrationTestCase
{
    public $fixtures = [
        'plugin.Bookmarks.Bookmark',
        'app.Category',
        'app.Draft',
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

    public function testAddFailureNoAuthorization()
    {
        $this->configRequest([
            'headers' => ['Accept' => 'application/json'],
        ]);

        $this->expectException(UnauthenticatedException::class);

        $data = ['pid' => 1, 'subject' => 'foo'];
        $this->post('api/v2/postings/', $data);
    }

    public function testAddSuccess()
    {
        $this->loginJwt(3);

        $data = ['pid' => 1, 'subject' => 'foo'];

        $EntriesTable = TableRegistry::get('Entries');
        $latestEntry = $EntriesTable->find()->order(['id' => 'desc'])->first();
        $expectedId = $latestEntry->get('id') + 1;

        $this->post('api/v2/postings/', $data);

        $this->assertResponseCode(200);
        $response = json_decode((string)$this->_response->getBody(), true);
        $this->assertArrayHasKey('pid', $response['data']['attributes']);
        $this->assertArrayHasKey('tid', $response['data']['attributes']);
        $this->assertSame(1, $response['data']['attributes']['pid']);
        $this->assertSame(1, $response['data']['attributes']['tid']);

        $latestEntry = $EntriesTable->find()->order(['id' => 'desc'])->first();
        $this->assertEquals($expectedId, $latestEntry->get('id'));
    }

    public function testAddFailureUnknownSaveIssue()
    {
        $this->loginJwt(3);

        $this->getMockOnController('Posting', ['create'])
            ->expects($this->once())
            ->method('create')
            ->will($this->returnValue(null));

        $this->expectException(BadRequestException::class);

        $this->post('api/v2/postings/', []);
    }

    public function testAddValidationErrorsCategoryMissing()
    {
        $this->loginJwt(3);
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionCode(1573123345);

        $this->post('api/v2/postings/', ['subject' => 'foo']);
    }

    public function testAddValidationErrorsSubjectMissing()
    {
        $this->loginJwt(3);

        $this->post('api/v2/postings/', ['category_id' => 3]);

        $this->assertResponseCode(200);
        $response = json_decode((string)$this->_response->getBody(), true);

        $this->assertArrayHasKey('errors', $response);

        $pointers = array_flip(Hash::extract($response, 'errors.{n}.source.pointer'));
        $this->assertArrayHasKey('/data/attributes/subject', $pointers);
    }

    public function testAddValidationErrorSubjectToLong()
    {
        $this->loginJwt(1);

        $data = [
            'category_id' => 1,
            // 41 chars (40 allowed)
            'subject' => 'Vorher wie ich in der mobilen Version ka…',
        ];

        $this->post('api/v2/postings/', $data);

        $this->assertResponseCode(200);
        $response = json_decode((string)$this->_response->getBody(), true);

        $this->assertArrayHasKey('errors', $response);
        $this->assertEquals('Subject: Subject is to long', $response['errors'][0]['title']);
    }

    public function testMetaFailureAuthorization()
    {
        $this->configRequest([
            'headers' => ['Accept' => 'application/json'],
        ]);

        $this->expectException(UnauthenticatedException::class);
        $this->get('api/v2/postingmeta');
    }

    public function testMetaCommon()
    {
        $this->loginJwt(3);
        $this->get('api/v2/postingmeta?pid=0');
        $response = json_decode((string)$this->_response->getBody(), true);

        $this->assertResponseCode(200);

        $this->assertArrayHasKey('editor', $response);
        $this->assertArrayHasKey('buttons', $response['editor']);
        $this->assertArrayHasKey('categories', $response['editor']);
        $this->assertArrayHasKey('smilies', $response['editor']);

        $this->assertArrayHasKey('meta', $response);
        $this->assertArrayHasKey('info', $response['meta']);
        $this->assertArrayHasKey('quoteSymbol', $response['meta']);
        $this->assertArrayHasKey('subjectMaxLength', $response['meta']);
        $this->assertFalse($response['meta']['autoselectCategory']);

        $this->assertArrayHasKey('posting', $response);
        $this->assertEquals([], $response['posting']);

        $this->assertArrayNotHasKey('draft', $response);
    }

    public function testMetaDraft()
    {
        $this->loginJwt(1);

        $this->get('api/v2/postingmeta/?pid=0');
        $response = json_decode((string)$this->_response->getBody(), true);

        $expected = [
                'id' => 2,
                'subject' => 'Draft Subject 2',
                'text' => 'Draft Text 2',
        ];
        $this->assertEquals($expected, $response['draft']);
    }

    public function testMetaAnswer()
    {
        $this->loginJwt(1);
        $this->get('api/v2/postingmeta/?pid=1');
        $response = json_decode((string)$this->_response->getBody(), true);

        $this->assertEquals('First_Subject', $response['meta']['subject']);
        $this->assertEquals('> First_Text', $response['meta']['text']);
    }

    public function testMetaEdit()
    {
        $this->loginJwt(1);
        $this->get('api/v2/postingmeta/1');
        $response = json_decode((string)$this->_response->getBody(), true);

        $this->assertEquals(1, $response['posting']['id']);
        $this->assertEquals(0, $response['posting']['pid']);
        $this->assertEquals(2, $response['posting']['category_id']);
        $this->assertEquals('First_Subject', $response['posting']['subject']);
        $this->assertEquals('First_Text', $response['posting']['text']);
        $this->assertEquals('2000-01-01T20:00:00+00:00', $response['posting']['time']);
    }

    public function testMetaAddForbiddenCategory()
    {
        $this->loginJwt(3);
        $this->expectException(SaitoForbiddenException::class);
        $this->get('api/v2/postingmeta/?pid=6');
    }

    public function testMetaEditForbiddenCategory()
    {
        $this->loginJwt(3);
        $this->expectException(SaitoForbiddenException::class);
        $this->get('api/v2/postingmeta/6');
    }

    public function testEditFailureUnauthorized()
    {
        $this->configRequest([
            'headers' => ['Accept' => 'application/json'],
        ]);

        $this->expectException(UnauthenticatedException::class);

        $this->put('api/v2/postings/9999', []);
    }

    public function testEditSuccess()
    {
        $this->loginJwt(1);

        $newSubject = 'hot';
        $newText = 'fuzz';
        $data = ['id' => 2, 'subject' => $newSubject, 'text' => $newText];

        $this->put('api/v2/postings/2', $data);

        $this->assertResponseCode(200);
        $response = json_decode((string)$this->_response->getBody(), true);
        $this->assertArrayHasKey('pid', $response['data']['attributes']);
        $this->assertArrayHasKey('tid', $response['data']['attributes']);
        $this->assertSame(1, $response['data']['attributes']['pid']);
        $this->assertSame(1, $response['data']['attributes']['tid']);

        $EntriesTable = TableRegistry::get('Entries');
        $posting = $EntriesTable->get(2);
        $this->assertEquals($newSubject, $posting->get('subject'));
        $this->assertEquals($newText, $posting->get('text'));
    }

    public function testEditFailureNoId()
    {
        $this->loginJwt(1);
        $data = ['subject' => 'foo'];

        $this->expectException(BadRequestException::class);

        $this->put('api/v2/postings/1', $data);
    }

    public function testEditFailureNoPosting()
    {
        $this->loginJwt(1);
        $data = ['id' => 9999, 'subject' => 'foo'];

        $this->expectException(RecordNotFoundException::class);

        $this->put('api/v2/postings/9999', $data);
    }

    public function testEditFailureUnknownPersistentError()
    {
        $this->loginJwt(1);
        $data = ['id' => 1, 'subject' => 'foo'];

        $entries = $this->getMockOnController('Posting', ['update'])
            ->expects($this->once())
            ->method('update')
            ->will($this->returnValue(null));

        $this->expectException(BadRequestException::class);

        $this->put('api/v2/postings/1', $data);
    }

    public function testEditValidationErrorsSubject()
    {
        $this->loginJwt(1);

        $data = ['id' => 1, 'subject' => 'Vorher wie ich in der mobilen Version ka…'];

        $this->put('api/v2/postings/1', $data);

        $this->assertResponseCode(200);
        $response = json_decode((string)$this->_response->getBody(), true);

        $this->assertArrayHasKey('errors', $response);
        $this->assertEquals('Subject: Subject is to long', $response['errors'][0]['title']);
    }
}
