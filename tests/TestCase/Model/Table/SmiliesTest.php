<?php

namespace Saito\Test\Model\Table;

use Saito\Cache\CacheSupport;

class SmiliesTest extends SaitoTableTestCase
{
    public $tableClass = 'Smilies';

    public $fixtures = ['app.smiley', 'app.smiley_code'];

    public function testLoad()
    {
        $expected = [
            [
                'sort' => 1,
                'icon' => 'wink.svg',
                'image' => 'wink.svg',
                'title' => 'Wink',
                'code' => ';-)',
                'type' => 'image'
            ],
            [
                'sort' => 1,
                'icon' => 'wink.svg',
                'image' => 'wink.svg',
                'title' => 'Wink',
                'code' => ';)',
                'type' => 'image'
            ],
            [
                'sort' => 2,
                'icon' => 'smile_icon.png',
                'image' => 'smile_image.png',
                'title' => 'Smile',
                'code' => ':-)',
                'type' => 'image'
            ],
            [
                'sort' => 3,
                'icon' => 'coffee',
                'image' => 'coffee',
                'title' => 'Coffee',
                'code' => '[_]P',
                'type' => 'font'
            ]
        ];
        $result = $this->Table->load();
        $this->assertEquals($expected, $result);
    }

    public function testCacheClearAfterDelete()
    {
        $this->Table = $this->getMockforTable('Smilies', ['clearCache']);
        $this->Table->expects($this->once())
            ->method('clearCache');
        $Entity = $this->Table->get(1);
        $this->Table->delete($Entity);
    }

    public function testCacheClearAfterSave()
    {
        $this->Table = $this->getMockforTable('Smilies', ['clearCache']);
        $this->Table->expects($this->once())
            ->method('clearCache');
        $Entity = $this->Table->get(1);
        $Entity->set('code', '?:');
        $this->Table->save($Entity);
    }

    public function setUp()
    {
        parent::setUp();
        $this->Table->clearCache();
    }

    public function tearDown()
    {
        $this->Table->clearCache();
        parent::tearDown();
    }

}
