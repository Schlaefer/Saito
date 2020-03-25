<?php

namespace App\Test\TestCase\View\Helper;

use App\View\Helper\UserHelper;
use Cake\TestSuite\TestCase;
use Cake\View\View;

class CurrencyRendererHelperTest extends TestCase
{
    /**
     * Helper to test
     *
     * @var UserHelper
     */
    public $helper = null;

    public function setUp()
    {
        parent::setUp();
        $View = new View();
        $this->helper = new UserHelper($View);
    }

    public function testLinkExternalHomepageEscapeIfNoLink()
    {
        $actual = $this->helper->linkExternalHomepage('<');
        $this->assertEquals('&lt;', $actual);
    }

    public function testLinkExternalHomepageLinkHttp()
    {
        $actual = $this->helper->linkExternalHomepage('http://tempest.island');
        $this->assertHtml(
            [
                'a' => ['href' => 'http://tempest.island'],
                'i' => ['class' => 'fa fa-home fa-lg'],
                '/i',
                '/a',
            ],
            $actual
        );
    }

    public function testLinkExternalHomepageLinkWww()
    {
        $actual = $this->helper->linkExternalHomepage('www.tempest.island');
        $this->assertHtml(['a' => ['href' => 'http://www.tempest.island']], $actual);
    }
}
