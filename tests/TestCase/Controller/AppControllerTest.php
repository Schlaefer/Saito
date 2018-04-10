<?php

namespace App\Test\TestCase\Controller;

use Cake\Core\Configure;
use Cake\Network\Request;
use Saito\Test\IntegrationTestCase;

class AppControllerTest extends IntegrationTestCase
{
    public $fixtures = [
        'app.category',
        'app.entry',
        'app.esevent',
        'app.esnotification',
        'app.setting',
        'app.shout',
        'app.smiley',
        'app.smiley_code',
        'app.upload',
        'app.user',
        'app.user_block',
        'app.user_ignore',
        'app.user_online',
        'app.user_read',
        'plugin.bookmarks.bookmark'
    ];

    /**
     * Test empty titleForLayout
     */
    public function testSetTitleForLayoutEmpty()
    {
        $this->get('/entries/index');
        $result = $this->viewVariable('titleForLayout');
        $this->assertEquals('Forum – macnemo', $result);
        $result = $this->viewVariable('titleForPage');
        $this->assertEquals('Forum', $result);
        $result = $this->viewVariable('forumName');
        $this->assertEquals('macnemo', $result);
    }

    /**
     * test nonempty titleForLayout
     */
    public function testSetTitleForLayoutNotEmpty()
    {
        $this->get('/entries/view/1');
        $result = $this->viewVariable('titleForLayout');
        $this->assertEquals('First_Subject | Ontopic – macnemo', $result);
    }

    /**
     * test empty title for layout with page_titles.po set
     */
    public function testSetTitleForLayoutPoFile()
    {
        $this->get('/users/register');
        $result = $this->viewVariable('titleForLayout');
        $this->assertEquals('Register – macnemo', $result);
    }

    public function testLocalReferer()
    {
        $this->markTestIncomplete('@td 3');
        $this->get('/entries/index');

        $baseUrl = 'http://cakephp.org/';
        Configure::write('App.fullBaseUrl', $baseUrl);

        $webroot = $this->_controller->request->webroot;

        $_SERVER['HTTP_REFERER'] = $baseUrl . $webroot . '/entries/index';
        $this->_controller->request = new Request();
        $result = $this->_controller->localReferer();
        $expected = '/entries/index';
        $this->assertEquals($expected, $result);

        $_SERVER['HTTP_REFERER'] = $baseUrl . $webroot . '/entries/view';
        $this->_controller->request = new Request();
        $result = $this->_controller->localReferer('action');
        $expected = 'view';
        $this->assertEquals($expected, $result);

        $_SERVER['HTTP_REFERER'] = $baseUrl . $webroot . '/some/path';
        $this->_controller->request = new Request();
        $result = $this->_controller->localReferer('controller');
        $expected = 'Some';
        $this->assertEquals($expected, $result);

        $_SERVER['HTTP_REFERER'] = $baseUrl . $webroot . '/some/';
        $this->_controller->request = new Request();
        $result = $this->_controller->localReferer('action');
        $expected = 'index';
        $this->assertEquals($expected, $result);

        $_SERVER['HTTP_REFERER'] = $baseUrl . $webroot . '';
        $this->_controller->request = new Request();
        $result = $this->_controller->localReferer('action');
        $expected = 'index';
        $this->assertEquals($expected, $result);

        $_SERVER['HTTP_REFERER'] = $baseUrl . $webroot . '';
        $this->_controller->request = new Request();
        $result = $this->_controller->localReferer('controller');
        $expected = 'Entries';
        $this->assertEquals($expected, $result);

        //* external referer
        $_SERVER['HTTP_REFERER'] = 'http://heise.de/foobar/baz.html';
        $this->_controller->request = new Request();
        $result = $this->_controller->localReferer('controller');
        $expected = 'Entries';
        $this->assertEquals($expected, $result);

        $_SERVER['HTTP_REFERER'] = 'http://heise.de/foobar/baz.html';
        $this->_controller->request = new Request();
        $result = $this->_controller->localReferer('action');
        $expected = 'index';
        $this->assertEquals($expected, $result);
    }
}
