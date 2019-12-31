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
use Cake\Http\Exception\BadRequestException;
use Cake\Http\Exception\NotFoundException;
use Cake\ORM\TableRegistry;
use Saito\Exception\SaitoForbiddenException;
use Saito\Test\IntegrationTestCase;

class DraftsControllerTest extends IntegrationTestCase
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
        'app.UserRead'
    ];

    public function testAddFailureNoAuthorization()
    {
        $this->expectException(UnauthenticatedException::class);

        $this->configRequest([
            'headers' => ['Accept' => 'application/json']
        ]);

        $data = ['subject' => 'foo', 'text' => 'bar'];
        $this->post('api/v2/drafts/', $data);
    }

    public function testDraftAddSuccess()
    {
        $this->loginJwt(3);
        $table = TableRegistry::getTableLocator()->get('Drafts');
        $draft = $table->find()->where(['user_id' => 3, 'pid' => 0])->first();
        $this->assertEmpty($draft);

        $data = ['pid' => 0, 'subject' => 'foo', 'text' => 'bar'];
        $this->post('/api/v2/drafts/', $data);

        $draft = $table->find()->where(['user_id' => '3', 'pid' => 0])->first();
        $this->assertEquals('foo', $draft->get('subject'));
        $this->assertEquals('bar', $draft->get('text'));

        $this->assertResponseOk();
        $response = json_decode((string)$this->_response->getBody(), true);
        $expected = [
            'data' => [
                'id' => 3,
                'type' => 'drafts',
                'attributes' => [
                    'id' => 3,
                ],
            ],
        ];
        $this->assertEquals($expected, $response);
    }

    public function testAddFailureDoubleEntry()
    {
        $this->loginJwt(1);
        $data = ['subject' => 'foo', 'text' => 'bar'];

        $this->expectException(BadRequestException::class);

        $this->post('/api/v2/drafts/', $data);
    }

    public function testEditFailureNoAuthorization()
    {
        $this->expectException(UnauthenticatedException::class);

        $this->configRequest([
            'headers' => ['Accept' => 'application/json']
        ]);

        $data = ['subject' => 'foo', 'text' => 'bar'];
        $this->put('api/v2/drafts/1', $data);
    }

    public function testEditSuccess()
    {
        $this->loginJwt(3);
        $table = TableRegistry::getTableLocator()->get('Drafts');
        $id = 1;

        $data = ['subject' => 'foo', 'text' => 'bar'];
        $this->put('/api/v2/drafts/' . $id, $data);

        $draft = $table->get($id);
        $this->assertEquals('foo', $draft->get('subject'));
        $this->assertEquals('bar', $draft->get('text'));

        $this->assertResponseCode(200);
    }

    public function testEditFailureDraftDoesNotExist()
    {
        $this->loginJwt(3);

        $this->expectException(NotFoundException::class);

        $this->put('/api/v2/drafts/9999', []);
    }

    public function testEditFailureDraftDoesNotBelongToUser()
    {
        $this->loginJwt(3);

        $this->expectException(SaitoForbiddenException::class);

        $this->put('/api/v2/drafts/2', []);
    }

    public function testEditSuccessDeleteDraftIfSubjectAndTextIsEmpty()
    {
        $this->loginJwt(3);
        $table = TableRegistry::getTableLocator()->get('Drafts');
        $id = 1;

        $this->assertTrue($table->exists(['id' => $id]));

        $data = ['subject' => '', 'text' => ''];
        $this->put('/api/v2/drafts/' . $id, $data);

        $this->assertFalse($table->exists(['id' => $id]));

        $this->assertResponseOk();
        $response = json_decode((string)$this->_response->getBody(), true);
        $expected = [
            'data' => [
                'id' => null,
                'type' => 'drafts',
                'attributes' => [
                    'id' => null,
                ],
            ],
        ];
        $this->assertEquals($expected, $response);
    }
}
