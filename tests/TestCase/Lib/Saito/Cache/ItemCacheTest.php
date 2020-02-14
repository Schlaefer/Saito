<?php
declare(strict_types=1);

namespace Saito\Test\Cache;

use App\Lib\Saito\Test\Cache\ItemCacheMock;
use Saito\Cache\SaitoCacheEngineAppCache;
use Saito\Test\SaitoTestCase;

class ItemCacheTest extends SaitoTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->_setupItemCache();

        $this->time = time();
    }

    public function tearDown(): void
    {
        parent::tearDown();
        $this->_cleanUp();
    }

    protected function _setFixture()
    {
        $this->ItemCache->set(1, 'foo', $this->time - 3600);

        $this->fixture = [
            1 => [
                'metadata' => [
                    'created' => $this->time,
                    'content_last_updated' => $this->time - 3600,
                ],
                'content' => 'foo',
            ],
        ];
    }

    protected function _setupItemCache($methods = null, array $options = [])
    {
        $this->_cleanUp();
        $this->ItemCache = $this->getMockBuilder(ItemCacheMock::class)
            ->setConstructorArgs(['test', null, $options])
            ->setMethods($methods)
            ->getMock();
        $this->CacheEngine = $this->getMockBuilder(SaitoCacheEngineAppCache::class)
            ->setMethods(['read', 'write'])
            ->getMock();
        $this->ItemCache->setCacheEngine($this->CacheEngine);
    }

    protected function _cleanUp()
    {
        unset($this->ItemCache);
        unset($this->CacheEngine);
    }

    public function testGcMaxItems()
    {
        $this->_setupItemCache(
            null,
            ['maxItems' => 2, 'maxItemsFuzzy' => 0]
        );

        $this->ItemCache->reset();
        $this->ItemCache->set('2', 'foo', 2);
        $this->ItemCache->set('4', 'bar', 4);
        $this->ItemCache->set('1', 'baz', 1);
        $this->ItemCache->set('3', 'baz', 3);

        $this->ItemCache->write();

        $cache = $this->ItemCache->get();
        $this->assertCount(2, $cache);
        $this->assertArrayHasKey('3', $cache);
        $this->assertArrayHasKey('4', $cache);
    }

    public function testGcOutdated()
    {
        $duration = 3600;
        $this->_setupItemCache(null, ['duration' => $duration]);

        $data = [
            0 => [
                'metadata' => [
                    'created' => $this->time - $duration - 2,
                    'content_last_updated' => $this->time,
                ],
                'content' => 'foo',
            ],
            1 => [
                'metadata' => [
                    'created' => $this->time - $duration - 1,
                    'content_last_updated' => $this->time,
                ],
                'content' => 'foo',
            ],
            2 => [
                'metadata' => [
                    'created' => $this->time - $duration,
                    'content_last_updated' => $this->time,
                ],
                'content' => 'foo',
            ],
            3 => [
                'metadata' => [
                    'created' => $this->time - $duration + 1,
                    'content_last_updated' => $this->time,
                ],
                'content' => 'foo',
            ],
        ];

        $this->CacheEngine->expects($this->once())->method('read')
            ->will($this->returnValue($data));

        $cache = $this->ItemCache->get();
        $this->assertArrayNotHasKey(0, $cache);
        $this->assertArrayNotHasKey(1, $cache);
        $this->assertArrayHasKey(2, $cache);
        $this->assertArrayHasKey(3, $cache);
    }

    public function testGetRaw()
    {
        $this->_setFixture();
        $this->assertEquals($this->fixture, $this->ItemCache->get());
    }

    public function testReset()
    {
        $this->ItemCache->reset();
        $this->assertEquals([], $this->ItemCache->get());
    }
}
