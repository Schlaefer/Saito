<?php

declare(strict_types = 1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace ImageUploader\Test\TestCase\Lib;

use Cake\TestSuite\TestCase;
use ImageUploader\Lib\UploaderConfig;

class UploaderConfigTest extends TestCase
{
    public function testDefaultSize()
    {
        $config = new UploaderConfig();
        $config->addType('video/webm');
        $this->assertEquals(2000000, $config->getSize('video/webm'));

        $config->setDefaultMaxFileSize(5);
        $config->addType('text/plain');
        $this->assertEquals(5, $config->getSize('text/plain'));

        $config->setDefaultMaxFileSize('3KB');
        $config->addType('text/plain');
        $this->assertEquals(3 * 1024, $config->getSize('text/plain'));
    }

    public function testCustomSize()
    {
        $config = new UploaderConfig();

        $config->addType('text/plain', 10);
        $this->assertEquals(10, $config->getSize('text/plain'));

        $config->addType('text/plain', '10KB');
        $this->assertEquals(10 * 1024, $config->getSize('text/plain'));
    }

    public function testSizeNotFound()
    {
        $config = new UploaderConfig();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionCode(1561357996);

        $config->getSize('foo/bar');
    }

    public function testGetCacheKey()
    {
        $config = new UploaderConfig();

        $this->assertEquals('uploadsThumbnails', $config->getCacheKey());
    }

    public function testGetAllTypes()
    {
        $config = new UploaderConfig();
        $types = ['text/plain', 'video/webm'];
        foreach ($types as $type) {
            $config->addType($type);
        }
        $this->assertEquals($types, $config->getAllTypes());
    }

    public function testMaxNumberOfUploadsPerUser()
    {
        $config = new UploaderConfig();
        $this->assertEquals(10, $config->getMaxNumberOfUploadsPerUser());

        $config->setMaxNumberOfUploadsPerUser(20);
        $this->assertEquals(20, $config->getMaxNumberOfUploadsPerUser());
    }

    public function testHasType()
    {
        $config = new UploaderConfig();
        $this->assertFalse($config->hasType('text/plain'));

        $config->addType('text/plain');
        $this->assertTrue($config->hasType('text/plain'));
    }
}
