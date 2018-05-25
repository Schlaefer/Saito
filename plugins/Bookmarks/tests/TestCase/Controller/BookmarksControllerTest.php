<?php

declare(strict_types = 1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers 2018
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace Bookmarks\Test\TestCase\Controller;

use Cake\Http\Exception\UnauthorizedException;
use Cake\ORM\TableRegistry;
use Cake\Utility\Hash;
use Saito\Test\IntegrationTestCase;

class BookmarksControllerTest extends IntegrationTestCase
{

    public $fixtures = [
        'app.category',
        'app.entry',
        'app.setting',
        'app.smiley',
        'app.smiley_code',
        'app.user',
        'app.user_block',
        'app.user_ignore',
        'app.user_online',
        'app.user_read',
        'plugin.bookmarks.bookmark'
    ];

    /**
     * @var \Cake\ORM\Table
     */
    protected $Bookmarks;

    public function setUp()
    {
        $this->Bookmarks = TableRegistry::get('Bookmarks');
        parent::setUp();
    }

    public function testIndexNoAuthorization()
    {
        $this->expectException(UnauthorizedException::class);

        $this->get('api/v2/bookmarks');
    }

    public function testIndexSuccess()
    {
        $userId = 3;
        $this->loginJwt($userId);

        $this->get('api/v2/bookmarks');

        $response = json_decode((string)$this->_response->getBody(), true);

        $this->assertNotEmpty($response['data'][0]['attributes']['threadline_html']);
        $response = Hash::remove($response, 'data.{n}.attributes.threadline_html');

        $this->assertResponseCode(200);

        $expected = [
            'data' => [
                [
                    'id' => 2,
                    'type' => 'bookmarks',
                    'attributes' => [
                        'id' => 2,
                        'entry_id' => 11,
                        'user_id' => 3,
                        'comment' => '< Comment 2',
                    ],
                ],
                [
                    'id' => 1,
                    'type' => 'bookmarks',
                    'attributes' => [
                        'id' => 1,
                        'entry_id' => 1,
                        'user_id' => 3,
                        'comment' => '',
                    ],
                ],
            ],
        ];

        $this->assertEquals($expected, $response);
        //// User has lost read access to one category with a bookmarked posting
        $count = count($expected['data']);
        $this->assertEquals(++$count, $this->Bookmarks->findByUserId($userId)->count());
    }

    public function testEditFailureNotLoggedIn()
    {
        $this->expectException(UnauthorizedException::class);

        $this->put('api/v2/bookmarks/1');
    }

    public function testEditFailureNotUsersBookmark()
    {
        $userId = 3;
        $this->loginJwt($userId);

        $this->expectException('Saito\Exception\SaitoForbiddenException');

        $data = [
            'id' => 1,
            'user_id' => 1,
            'entry_id' => 1,
            'comment' => 'foo',
        ];

        $this->put('api/v2/bookmarks/3', $data);
    }

    public function testEditSuccess()
    {
        $userId = 3;
        $this->loginJwt($userId);

        $data = [
            'id' => 3,
            'user_id' => 1,
            'entry_id' => 3,
            'comment' => 'new < & comment',
        ];

        $this->put('api/v2/bookmarks/1', $data);

        $entity = $this->Bookmarks->get(1);
        $this->assertEquals(1, $entity->get('id'));
        $this->assertEquals(3, $entity->get('user_id'));
        $this->assertEquals(1, $entity->get('entry_id'));
        $this->assertEquals('new < & comment', $entity->get('comment'));

        $this->assertResponseCode(204);
    }

    public function testDeleteFailureNotLoggedIn()
    {
        $this->expectException(UnauthorizedException::class);

        $this->delete('api/v2/bookmarks/1');
    }

    public function testDeleteFailureNotUsersBookmark()
    {
        $userId = 3;
        $this->loginJwt($userId);

        $this->expectException('Saito\Exception\SaitoForbiddenException');

        $this->delete('api/v2/bookmarks/3');
    }

    public function testDeleteSuccess()
    {
        $userId = 3;
        $this->loginJwt($userId);
        $this->assertTrue($this->Bookmarks->exists(['id' => 1]));

        $this->delete('api/v2/bookmarks/1');

        $this->assertResponseCode(204);
        $this->assertFalse($this->Bookmarks->exists(['id' => 1]));
    }

    public function testAddFailureNotLoggedIn()
    {
        $this->expectException(UnauthorizedException::class);

        $this->post('api/v2/bookmarks/');
    }

    public function testAddFailureBookmarkExists()
    {
        $entryId = 11;
        $userId = 3;
        $this->loginJwt($userId);

        $before = $this->Bookmarks->find()->count();
        $this->assertTrue($this->Bookmarks->exists(['entry_id' => $entryId]));

        $this->post('api/v2/bookmarks/', ['entry_id' => $entryId, 'user_id' => '1']);

        $this->assertEquals($before, $this->Bookmarks->find()->count());
        $this->assertTrue($this->Bookmarks->exists(['entry_id' => $entryId]));
    }

    public function testAddSuccess()
    {
        $userId = 3;
        $this->loginJwt($userId);

        $before = $this->Bookmarks->find()->count();
        $this->assertFalse($this->Bookmarks->exists(['entry_id' => 4, 'user_id' => 3]));

        $this->post('api/v2/bookmarks/', ['entry_id' => '4', 'user_id' => '1']);

        $this->assertEquals($before + 1, $this->Bookmarks->find()->count());
        $this->assertTrue($this->Bookmarks->exists(['entry_id' => 4, 'user_id' => 3]));

        $this->assertResponseCode(200);
        $response = json_decode((string)$this->_response->getBody(), true);
        $expected = [
            'data' => [
                'id' => 6,
                'type' => 'bookmarks',
                'attributes' => [
                    'id' => 6,
                    'entry_id' => 4,
                    'user_id' => 3,
                    'comment' => null,
                ],
            ],
        ];
        $this->assertEquals($expected, $response);
    }
}
