<?php

namespace Saito\Test\Smiley;

use Cake\Cache\Cache;
use Cake\View\Helper\HtmlHelper;
use Cake\View\View;
use Saito\Smiley\SmileyLoader;
use Saito\Smiley\SmileyRenderer;
use Saito\Test\SaitoTestCase;

class SmileyRenderTest extends SaitoTestCase
{

    public $fixtures = ['app.Smiley', 'app.SmileyCode'];

    public function setUp(): void
    {
        //= smiley fixture
        $smiliesFixture = [
            [
                'order' => 1,
                'icon' => 'wink.png',
                'image' => 'wink.png',
                'title' => 'Wink',
                'code' => ';)',
                'type' => 'image',
            ],
            [
                'order' => 2,
                'icon' => 'smile_icon.svg',
                'image' => 'smile_image.svg',
                'title' => 'Smile',
                'code' => ':-)',
                'type' => 'image',
            ],
            [
                'order' => 3,
                'icon' => 'coffee',
                'image' => 'coffee',
                'title' => 'Coffee',
                'code' => '[_]P',
                'type' => 'font',
            ],
        ];
        Cache::write('Saito.Smilies.data', $smiliesFixture);

        $loader = new SmileyLoader();
        $this->Renderer = new SmileyRenderer($loader);

        $View = new View();
        $this->Helper = new HtmlHelper($View);

        $this->Renderer->setHelper($this->Helper);
    }

    public function tearDown(): void
    {
        unset($this->Helper, $this->Renderer);
    }

    public function testSmiliesPixelImage()
    {
        $input = ';)';
        $expected = [
            'img' => [
                'src' => $this->Helper->getView()->getRequest()->getAttribute('webroot') . 'img/smilies/wink.png',
                'alt' => ';)',
                'class' => 'saito-smiley-image',
                'title' => 'Wink',
            ],
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
                'title' => 'Coffee',
            ],
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
                'src' => $this->Helper->getView()->getRequest()->getAttribute('webroot') . 'img/smilies/wink.png',
                'alt' => ';)',
                'class' => 'saito-smiley-image',
                'title' => 'Wink',
            ],
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
