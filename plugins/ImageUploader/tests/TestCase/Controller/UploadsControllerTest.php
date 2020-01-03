<?php

declare(strict_types=1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace ImageUploader\Test\TestCase\Controller;

use Api\Error\Exception\GenericApiException;
use Authentication\Authenticator\UnauthenticatedException;
use Cake\Core\Configure;
use Cake\Core\Plugin;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Filesystem\File;
use Cake\ORM\TableRegistry;
use ImageUploader\Model\Table\UploadsTable;
use Saito\Exception\SaitoForbiddenException;
use Saito\Test\IntegrationTestCase;

class UploadsControllerTest extends IntegrationTestCase
{
    public $fixtures = [
        'app.Category',
        'app.Entry',
        'app.Setting',
        'app.User',
        'app.UserBlock',
        'app.UserIgnore',
        'app.UserRead',
        'app.UserOnline',
        'plugin.ImageUploader.Uploads',
    ];

    /**
     * @var File dummy file
     */
    private $file;

    public function setUp()
    {
        parent::setUp();

        $this->file = new File(TMP . 'my new-upload.png');
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
        $this->expectException(UnauthenticatedException::class);

        $this->post('api/v2/uploads', []);
    }

    public function testAddFailureUploadBelongsToDifferentUser()
    {
        $this->loginJwt(3);

        $this->expectException(SaitoForbiddenException::class);

        $this->upload($this->file, 1);
    }

    public function testAddFailureUserDoesNotExist()
    {
        $this->loginJwt(1);

        $this->expectException(RecordNotFoundException::class);

        $this->upload($this->file, 9999);
    }

    /**
     * png is successfully uploaded and converted to jpeg
     */
    public function testAddSuccess()
    {
        $this->loginJwt(1);

        $this->upload($this->file, 1);
        $response = json_decode((string)$this->_response->getBody(), true);

        $this->assertResponseCode(200);

        $this->assertWithinRange(
            time(),
            strtotime($response['data']['attributes']['created']),
            3
        );
        unset($response['data']['attributes']['created']);

        $this->assertGreaterThan(0, $response['data']['attributes']['size']);
        unset($response['data']['attributes']['size']);

        $expected = [
            'data' => [
                'id' => 3,
                'type' => 'uploads',
                'attributes' => [
                    'id' => 3,
                    'mime' => 'image/jpeg',
                    'name' => '1_ebd536d37aff03f2b570329b20ece832.jpg',
                    'thumbnail_url' => '/api/v2/uploads/thumb/3?h=e1fddb2ea8f448fac14ec06b88d4ce94',
                    'title' => 'my new-upload.png',
                    'url' => '/useruploads/1_ebd536d37aff03f2b570329b20ece832.jpg',
                ],
            ],
        ];
        $this->assertEquals($expected, $response);

        $Uploads = TableRegistry::getTableLocator()->get('ImageUploader.Uploads');
        $upload = $Uploads->get(3);

        $this->assertSame('1_ebd536d37aff03f2b570329b20ece832.jpg', $upload->get('name'));
        $this->assertSame('image/jpeg', $upload->get('type'));
        $this->assertTrue($upload->get('file')->exists());
    }

    public function testAddSvg()
    {
        $this->loginJwt(1);

        $this->file = new File(TMP . 'tmp_svg.svg');
        $this->file->write('<?xml version="1.0" encoding="UTF-8" ?>
            <svg width="9" height="9" style="background:red;"></svg>');
        $this->upload($this->file);

        $response = json_decode((string)$this->_response->getBody(), true);

        $this->assertResponseCode(200);

        $this->assertWithinRange(
            time(),
            strtotime($response['data']['attributes']['created']),
            3
        );
        unset($response['data']['attributes']['created']);

        $expected = [
            'data' => [
                'id' => 3,
                'type' => 'uploads',
                'attributes' => [
                    'id' => 3,
                    'mime' => 'image/svg+xml',
                    'name' => '1_853fe7aa4ef213b0c11f4b739cf444a8.svg',
                    'size' => 108,
                    'thumbnail_url' => '/api/v2/uploads/thumb/3?h=1d57b148ad44d4caf90fa1cd98729678',
                    'title' => 'tmp_svg.svg',
                    'url' => '/useruploads/1_853fe7aa4ef213b0c11f4b739cf444a8.svg',
                ],
            ],
        ];
        $this->assertEquals($expected, $response);

        $Uploads = TableRegistry::getTableLocator()->get('ImageUploader.Uploads');
        $upload = $Uploads->get(3);

        $this->assertSame('1_853fe7aa4ef213b0c11f4b739cf444a8.svg', $upload->get('name'));
        $this->assertSame('image/svg+xml', $upload->get('type'));
        $this->assertTrue($upload->get('file')->exists());
    }

    public function testAddMimeTypeConversion()
    {
        $this->loginJwt(1);

        $this->file = new File(TMP . 'test.mp4');
        $fixture = new File(Plugin::path('ImageUploader') . 'tests/Fixture/test-application-octo.mp4');
        $fixture->copy($this->file->path);
        $this->assertEquals('application/octet-stream', $this->file->mime());

        $this->upload($this->file);

        $this->assertResponseOk();

        $Uploads = TableRegistry::getTableLocator()->get('ImageUploader.Uploads');
        $upload = $Uploads->get(3);
        $this->assertSame('test.mp4', $upload->get('title'));
        $this->assertSame('video/mp4', $upload->get('type'));
    }

    public function testRemoveExifData()
    {
        $this->loginJwt(1);
        unset($this->file);
        $this->file = new File(TMP . 'tmp_exif.jpg');

        $fixture = new File($path = Plugin::path('ImageUploader') . 'tests/Fixture/exif-with-location.jpg');
        $fixture->copy($this->file->path);

        $readExif = function (File $file) {
            //@codingStandardsIgnoreStart
            return @exif_read_data($file->path);
            //@codingStandardsIgnoreEnd
        };
        $exif = $readExif($this->file);
        $this->assertNotEmpty($exif['SectionsFound']);
        $this->assertContains('EXIF', $exif['SectionsFound']);
        $this->assertContains('IFD0', $exif['SectionsFound']);

        $this->upload($this->file);

        $response = json_decode((string)$this->_response->getBody(), true);

        $this->assertResponseCode(200);

        $Uploads = TableRegistry::getTableLocator()->get('ImageUploader.Uploads');
        $upload = $Uploads->find('all')->last();

        $exif = $readExif($upload->get('file'));
        $this->assertNotContains('EXIF', $exif['SectionsFound']);
        $this->assertNotContains('IFD0', $exif['SectionsFound']);
    }

    public function testAddFailureMaxUploadsPerUser()
    {
        Configure::read('Saito.Settings.uploader')->setMaxNumberOfUploadsPerUser(1);
        $this->loginJwt(1);

        $Uploads = TableRegistry::getTableLocator()->get('ImageUploader.Uploads');
        $count = $Uploads->find()->count();

        $this->expectException(GenericApiException::class);
        $this->expectExceptionMessage('Error: Reached the maximal number of 1 uploads.');

        $this->upload($this->file);

        $this->assertEquals($count, $Uploads->find()->count());
    }

    public function testAddFailureMaxDocumentSize()
    {
        Configure::read('Saito.Settings.uploader')
            ->setMaxNumberOfUploadsPerUser(10)
            ->addType('image/png', 10);

        $this->loginJwt(1);

        $Uploads = TableRegistry::getTableLocator()->get('ImageUploader.Uploads');
        $count = $Uploads->find()->count();

        $this->expectException(GenericApiException::class);
        $this->expectExceptionMessage('Error: File size exceeds allowed limit of 10 Bytes.');

        $this->upload($this->file);

        $this->assertEquals($count, $Uploads->find()->count());
    }

    public function testAddFailureDoubleUpload()
    {
        $this->loginJwt(1);
        // Make sure to test a file that may get transformed on upload (e.g. PNG
        // to JEPG).
        $file = new File(TMP . 'my new-upload.png');
        $this->mockMediaFile($file);
        $this->upload($file);

        $this->expectException(GenericApiException::class);
        $this->expectExceptionCode(400);
        $this->expectExceptionMessage('File with same name already uploaded');

        $this->loginJwt(1);
        $this->upload($file);

        $file->delete();
    }

    public function testAddFailureFilenameToLong()
    {
        $this->loginJwt(1);
        $max = UploadsTable::FILENAME_MAXLENGTH;
        $file = new File(TMP . str_pad('', $max + 1, '0') . '.png');
        $this->mockMediaFile($file);

        $this->expectException(GenericApiException::class);
        $this->expectExceptionCode(400);
        $this->expectExceptionMessage((string)$max);
        $this->upload($file);

        $file->delete();
    }

    public function testIndexNoAuthorization()
    {
        $this->expectException(UnauthenticatedException::class);

        $this->get('api/v2/uploads');
    }

    public function testIndexFailureUploadBelongsToDifferentUser()
    {
        $this->loginJwt(3);

        $this->expectException(SaitoForbiddenException::class);

        $this->get('api/v2/uploads/?id=1');
    }

    public function testIndexSuccess()
    {
        $this->loginJwt(3);

        $this->get('api/v2/uploads?id=3');

        $response = json_decode((string)$this->_response->getBody(), true);

        $this->assertResponseCode(200);

        $this->assertEquals(
            1526404380,
            strtotime($response['data'][0]['attributes']['created'])
        );
        unset($response['data'][0]['attributes']['created']);

        $expected = [
            'data' => [
                [
                    'id' => 2,
                    'type' => 'uploads',
                    'attributes' => [
                        'id' => 2,
                        'mime' => 'image/jpeg',
                        'name' => '3-another-upload.jpg',
                        'size' => 50000,
                        'thumbnail_url' => '/api/v2/uploads/thumb/2?h=be7ef71551c4245f82223d0c8e652eee',
                        'title' => '3-another-upload.jpg',
                        'url' => '/useruploads/3-another-upload.jpg',
                    ],
                ],
            ],
        ];
        $this->assertEquals($expected, $response);
    }

    public function testDeleteNoAuthorization()
    {
        $this->expectException(UnauthenticatedException::class);

        $this->delete('api/v2/uploads/1');
    }

    public function testDeleteFailureUploadBelongsToDifferentUser()
    {
        $this->loginJwt(3);

        $this->expectException(SaitoForbiddenException::class);

        $this->delete('api/v2/uploads/1');
    }

    public function testUploadDeleteFailureNotFound()
    {
        $this->loginJwt(1);
        $this->expectException(RecordNotFoundException::class);
        $this->delete('api/v2/uploads/9999');
    }

    public function testDeleteSuccess()
    {
        $this->loginJwt(1);
        $Uploads = TableRegistry::getTableLocator()->get('ImageUploader.Uploads');
        $upload = $Uploads->get(1);
        $this->assertNotEmpty($Uploads->get(1));
        $this->mockMediaFile($upload->get('file'));

        $this->delete('api/v2/uploads/1');

        $response = json_decode((string)$this->_response->getBody(), true);

        $this->assertResponseCode(204);

        $this->assertFalse($Uploads->exists(1));
    }

    /**
     * Sends a file to upload api
     *
     * @param File $file The file to send
     * @param mixed $userId The user-ID to upload to
     */
    private function upload(File $file, $userId = 1)
    {
        $data = [
            'upload' => [
                0 => [
                    'file' => [
                        'tmp_name' => $file->path,
                        'name' => $file->name() . '.' . $file->ext(),
                        'size' => $file->size(),
                        'type' => $file->mime(),
                    ]
                ]
            ]
        ];
        if ($userId) {
            $data['userId'] = (string)$userId;
        }
        $this->post('api/v2/uploads.json', $data);
    }
}
