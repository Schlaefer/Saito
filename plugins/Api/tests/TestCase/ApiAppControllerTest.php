<?php

use Api\Lib\ApiIntegrationTestCase;
use Cake\Core\Configure;

class ApiAppControllerTest extends ApiIntegrationTestCase
{

    protected $_apiRoot = 'api/v1/';

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'app.category',
        'app.entry',
        'app.esevent',
        'app.esnotification',
        'app.setting',
        'app.upload',
        'app.user',
        'app.user_block',
        'app.user_ignore',
        'app.user_online',
        'app.user_read',
        'plugin.bookmarks.bookmark'
    ];

    public function testApiDisabled()
    {
        Configure::write('Saito.Settings.api_enabled', '0');
        $this->expectException('Api\Error\Exception\ApiDisabledException');
        $this->get($this->_apiRoot . 'bootstrap.json');
    }

    public function testApiAllowOriginHeader()
    {
        $expected = rand();
        Configure::write('Saito.Settings.api_crossdomain', $expected);
        $this->get($this->_apiRoot . 'bootstrap.json');
        $header = $this->_response->header()['Access-Control-Allow-Origin'];
        $this->assertEquals($expected, $header);
    }

    public function testApiAllowOriginHeaderNotSet()
    {
        Configure::write('Saito.Settings.api_crossdomain', '');
        $this->get($this->_apiRoot . 'bootstrap.json');
        $headers = $this->_response->header();
        $this->assertFalse(isset($headers['Access-Control-Allow-Origin']));
    }
}
