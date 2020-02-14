<?php
declare(strict_types=1);

namespace Saito\Test\Smiley;

use Saito\Smiley\SmileyLoader;
use Saito\Test\SaitoTestCase;

class SmileyLoaderTest extends SaitoTestCase
{
    public $fixtures = ['app.Smiley', 'app.SmileyCode'];

    public function testLoad()
    {
        $loader = new SmileyLoader();
        $expected = [
            [
                'sort' => 1,
                'icon' => 'wink.svg',
                'image' => 'wink.svg',
                'title' => 'Wink',
                'code' => ';-)',
                'type' => 'image',
            ],
            [
                'sort' => 1,
                'icon' => 'wink.svg',
                'image' => 'wink.svg',
                'title' => 'Wink',
                'code' => ';)',
                'type' => 'image',
            ],
            [
                'sort' => 2,
                'icon' => 'smile_icon.png',
                'image' => 'smile_image.png',
                'title' => 'Smile',
                'code' => ':-)',
                'type' => 'image',
            ],
            [
                'sort' => 3,
                'icon' => 'coffee',
                'image' => 'coffee',
                'title' => 'Coffee',
                'code' => '[_]P',
                'type' => 'font',
            ],
        ];
        $result = $loader->get();
        $this->assertEquals($expected, $result);
    }
}
