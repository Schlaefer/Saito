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

use Cake\Cache\Cache;
use Cake\Core\Configure;
use Cake\Filesystem\File;
use Cake\ORM\TableRegistry;
use claviska\SimpleImage;
use Saito\Exception\SaitoForbiddenException;
use Saito\Test\IntegrationTestCase;

class ThumbnailControllerTest extends IntegrationTestCase
{
    public $fixtures = [
        'app.setting',
        'plugin.ImageUploader.uploads',
    ];

    public function testCacheCreation()
    {
        $Uploads = TableRegistry::get('ImageUploader.Uploads');
        $upload = $Uploads->get(1);

        $file = new File(Configure::read('Saito.Settings.uploadDirectory') . $upload->get('name'));
        $raw = (new SimpleImage())
            ->fromNew(500, 500, 'blue')
            ->toString($upload->get('type'));
        $file->write($raw);
        // pad image
        $file->append(str_repeat('0', $upload->get('size')));

        $this->assertFalse(Cache::read($upload->get('id'), 'uploadsThumbnails'));

        $this->get('/api/v2/uploads/thumb/1?h=' . $upload->get('hash'));

        $cache = Cache::read($upload->get('id'), 'uploadsThumbnails');

        $image = imagecreatefromstring($cache['raw']);
        $this->assertSame(300, imagesx($image));
        $this->assertSame(300, imagesy($image));
        $this->assertSame($upload->get('type'), $cache['type']);
        $this->assertResponseEquals($cache['raw'], (string)$this->_response->getBody());
        $this->assertHeader('content-type', 'image/png');

        //// cleanup
        $file->delete();
        unset($cache, $file);
    }

    /**
     * Test that an hash must be send with the thumbnail-URL
     *
     * The hash prevents reading out thumbnails by just increasing the image-id
     * in the URL.
     */
    public function testAccessFailureNoHash()
    {
        $Uploads = TableRegistry::get('ImageUploader.Uploads');
        $upload = $Uploads->get(1);

        $file = new File(Configure::read('Saito.Settings.uploadDirectory') . $upload->get('name'));
        $raw = (new SimpleImage())
            ->fromNew(100, 100, 'blue')
            ->toString($upload->get('type'));
        $file->write($raw);

        $this->expectException(SaitoForbiddenException::class);
        $this->get('/api/v2/uploads/thumb/1');
    }
}
