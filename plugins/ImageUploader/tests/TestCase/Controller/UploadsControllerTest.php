<?php

declare(strict_types = 1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers 2018
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace ImageUploader\Test\TestCase\Controller;

use Api\Error\Exception\GenericApiException;
use Cake\Cache\Cache;
use Cake\Core\Configure;
use Cake\Filesystem\File;
use Cake\Http\Exception\UnauthorizedException;
use Cake\ORM\TableRegistry;
use claviska\SimpleImage;
use Saito\Exception\SaitoForbiddenException;
use Saito\Test\IntegrationTestCase;

class UploadsControllerTest extends IntegrationTestCase
{
    public $fixtures = [
        'app.category',
        'app.entry',
        'app.setting',
        'app.user',
        'app.user_block',
        'app.user_read',
        'app.useronline',
        'plugin.ImageUploader.uploads',
    ];

    /**
     * @var File dummy file
     */
    private $file;

    public function setUp()
    {
        parent::setUp();

        $this->file = new File(TMP . 'my new_upload.png');
        $this->mockMediaFile($this->file);
    }

    public function tearDown()
    {
        $this->file->delete();
        unset($this->file);

        parent::tearDown();
    }

    public function testAddNotAuthorized()
    {
        $this->expectException(UnauthorizedException::class);

        $this->post('api/v2/uploads', []);
    }

    public function testAddSuccess()
    {
        $this->loginJwt(1);

        $data = [
            'upload' => [
                0 => [
                    'file' => [
                        'tmp_name' => $this->file->path,
                        'name' => $this->file->name() . '.' . $this->file->ext(),
                        'size' => $this->file->size(),
                        'type' => $this->file->mime(),
                    ]
                ]
            ]
        ];
        $this->post('api/v2/uploads.json', $data);

        $response = json_decode((string)$this->_response->getBody(), true);

        $this->assertResponseCode(200);

        $expected = [
            'data' => [
                'id' => 3,
                'type' => 'uploads',
                'attributes' => [
                    'id' => 3,
                    'name' => '1-my-new-upload.png',
                    'url' => '/useruploads/1-my-new-upload.png',
                    'thumbnail_url' => '/api/v2/uploads/thumb/3',
                ],
            ],
        ];
        $this->assertEquals($expected, $response);

        $Uploads = TableRegistry::get('ImageUploader.Uploads');
        $upload = $Uploads->get(3);

        $this->assertSame('1-my-new-upload.png', $upload->get('name'));
        $this->assertSame('image/png', $upload->get('type'));
        $this->assertTrue($upload->get('file')->exists());
    }

    public function testAddFailureMaxUploadsPerUser()
    {
        Configure::write('Saito.Settings.upload_max_number_of_uploads', 1);
        $this->loginJwt(1);

        $Uploads = TableRegistry::get('ImageUploader.Uploads');
        $count = $Uploads->find()->count();

        $this->expectException(GenericApiException::class);
        $this->expectExceptionMessage('Error: No more uploads possible (max: 1)');

        $data = [
            'upload' => [
                0 => [
                    'file' => [
                        'tmp_name' => $this->file->path,
                        'name' => $this->file->name(),
                        'size' => $this->file->size(),
                        'type' => $this->file->mime(),
                    ]
                ]
            ]
        ];
        $this->post('api/v2/uploads.json', $data);

        $this->assertEquals($count, $Uploads->find()->count());
    }

    public function testAddFailureMaxDocumentSize()
    {
        Configure::write('Saito.Settings.upload_max_img_size', 10);
        $this->loginJwt(1);

        $Uploads = TableRegistry::get('ImageUploader.Uploads');
        $count = $Uploads->find()->count();

        $this->expectException(GenericApiException::class);
        $this->expectExceptionMessage('Error: File size is over allowed limit of 10 kB');

        $data = [
            'upload' => [
                0 => [
                    'file' => [
                        'tmp_name' => $this->file->path,
                        'name' => $this->file->name(),
                        'size' => $this->file->size(),
                        'type' => $this->file->mime(),
                    ]
                ]
            ]
        ];
        $this->post('api/v2/uploads.json', $data);

        $this->assertEquals($count, $Uploads->find()->count());
    }

    public function testIndexNoAuthorization()
    {
        $this->expectException(UnauthorizedException::class);

        $this->get('api/v2/uploads');
    }

    public function testIndexSuccess()
    {
        $this->loginJwt(3);

        $this->get('api/v2/uploads');

        $response = json_decode((string)$this->_response->getBody(), true);

        $this->assertResponseCode(200);

        $expected = [
            'data' => [
                [
                    'id' => 2,
                    'type' => 'uploads',
                    'attributes' => [
                        'id' => 2,
                        'name' => '3-another-upload.jpg',
                        'url' => '/useruploads/3-another-upload.jpg',
                        'thumbnail_url' => '/api/v2/uploads/thumb/2',
                    ],
                ],
            ],
        ];
        $this->assertEquals($expected, $response);
    }

    public function testDeleteNoAuthorization()
    {
        $this->expectException(UnauthorizedException::class);

        $this->delete('api/v2/uploads/1');
    }

    public function testDeleteSuccess()
    {
        $this->loginJwt(1);
        $Uploads = TableRegistry::get('ImageUploader.Uploads');
        $upload = $Uploads->get(1);
        $this->assertNotEmpty($Uploads->get(1));
        $this->mockMediaFile($upload->get('file'));

        $this->delete('api/v2/uploads/1');

        $response = json_decode((string)$this->_response->getBody(), true);

        $this->assertResponseCode(204);

        $this->assertFalse($Uploads->exists(1));
    }

    public function testDeleteFailureUploadBelongsToDifferentUser()
    {
        $this->loginJwt(3);

        $this->expectException(SaitoForbiddenException::class);

        $this->delete('api/v2/uploads/1');
    }
}
