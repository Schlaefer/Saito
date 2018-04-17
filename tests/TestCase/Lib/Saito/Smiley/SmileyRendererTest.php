<?php

namespace Saito\Test\Smiley;

use App\View\Helper\ParserHelper;
use Cake\Cache\Cache;
use Cake\View\View;
use Saito\Smiley\SmileyLoader;
use Saito\Smiley\SmileyRenderer;
use Saito\Test\SaitoTestCase;

class SmileyRenderTest extends SaitoTestCase
{

    public $fixtures = ['app.smiley', 'app.smiley_code'];

    public function setUp()
    {
        //= smiley fixture
        $smiliesFixture = [
            [
                'order' => 1,
                'icon' => 'wink.png',
                'image' => 'wink.png',
                'title' => 'Wink',
                'code' => ';)',
                'type' => 'image'
            ],
            [
                'order' => 2,
                'icon' => 'smile_icon.svg',
                'image' => 'smile_image.svg',
                'title' => 'Smile',
                'code' => ':-)',
                'type' => 'image'
            ],
            [
                'order' => 3,
                'icon' => 'coffee',
                'image' => 'coffee',
                'title' => 'Coffee',
                'code' => '[_]P',
                'type' => 'font'
            ],
        ];
        Cache::write('Saito.Smilies.data', $smiliesFixture);

        $loader = new SmileyLoader();
        $this->Renderer = new SmileyRenderer($loader);

        $View = new View();
        $this->Helper = new ParserHelper($View);

        $this->Renderer->setHelper($this->Helper);
    }

    public function tearDown()
    {
        unset($this->Helper, $this->Renderer);
    }

    public function testSmiliesPixelImage()
    {
        $input = ';)';
        $expected = [
            'img' => [
                'src' => $this->Helper->request->getAttribute('webroot') . 'img/smilies/wink.png',
                'alt' => ';)',
                'class' => 'saito-smiley-image',
                'title' => 'Wink'
            ]
        ];
        $result = $this->Renderer->replace($input);
        $this->assertHtml($expected, $result);
    }

    public function testSmiliesVectorFont()
    {
        $input = '[_]P';
        $expected = [
            'i' => [
                'class' => 'saito-smiley-font saito-smiley-coffee',
                'title' => 'Coffee'
            ]
        ];
        $result = $this->Renderer->replace($input);
        $this->assertHtml($expected, $result);
    }

    /**
     * smilies should not be triggered next to HTML-entities
     */
    public function testNoSmileyReplacementNextToHtmlEntities()
    {
        //= test working wink
        $input = ';)';
        $expected = [
            'img' => [
                'src' => $this->Helper->request->getAttribute('webroot') . 'img/smilies/wink.png',
                'alt' => ';)',
                'class' => 'saito-smiley-image',
                'title' => 'Wink'
            ]
        ];
        $result = $this->Renderer->replace($input);
        $this->assertHtml($expected, $result);

        //= test that wink is not triggered on entities
        $input = '&quot;)';
        $expected = '&quot;)';
        $result = $this->Renderer->replace($input);
        $this->assertEquals($expected, $result);

        $input = '&lt;)';
        $expected = '&lt;)';
        $result = $this->Renderer->replace($input);
        $this->assertEquals($expected, $result);

        $input = '&gt;)';
        $expected = '&gt;)';
        $result = $this->Renderer->replace($input);
        $this->assertEquals($expected, $result);
    }
}
