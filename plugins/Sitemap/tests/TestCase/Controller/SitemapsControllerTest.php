<?php

namespace Sitemap\Test\TestCase\Controller;

use Cake\Cache\Cache;
use Cake\Routing\Router;
use Saito\Test\IntegrationTestCase;

class SitemapsControllerTest extends IntegrationTestCase
{
    public $fixtures = [
        'app.category',
        'app.entry',
        'app.setting',
        'app.user',
        'app.user_block',
        'app.useronline'
    ];

    /**
     * testIndex method
     *
     * basic test that at least something is in the output
     *
     * @return void
     */
    public function testIndex()
    {
        $this->get('/sitemap.xml');
        $baseUrl = Router::fullBaseUrl();
        $this->assertResponseContains(
            $baseUrl . '/sitemap/file/sitemap-entries-1-20000.xml'
        );
    }

    /**
     * testFile method
     *
     * basic test that at least something is in the output
     *
     * @return void
     */
    public function testFile()
    {
        $this->get('/sitemap/file/sitemap-entries-1-20000.xml');
        $baseUrl = Router::fullBaseUrl();
        $this->assertResponseContains("{$baseUrl}/entries/view/1</loc>");
        $this->assertResponseNotContains("{$baseUrl}/entries/view/4</loc>");
        $this->assertResponseNotContains("{$baseUrl}/entries/view/6</loc>");
    }

    public function setUp()
    {
        if (Cache::config('sitemap')) {
            Cache::clear(false, 'sitemap');
        }
        parent::setUp();
    }
}
