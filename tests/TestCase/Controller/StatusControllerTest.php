<?php

namespace App\Test\TestCase\Controller;

use Saito\Test\IntegrationTestCase;

class StatusControllerTest extends IntegrationTestCase
{

    public $fixtures = [
        'app.category',
        'app.entry',
        'app.esevent',
        'app.esnotification',
        'app.setting',
        'app.shout',
        'app.upload',
        'app.user',
        'app.user_block',
        'app.user_online',
        'app.user_read',
        'plugin.bookmarks.bookmark'
    ];

    public function testStatusMustBeAjax()
    {
        $this->setExpectedException(
            'Cake\Network\Exception\BadRequestException'
        );
        $this->get('/status/status');
    }

    public function testStatusSuccess()
    {
        $this->_setAjax();
        $this->_setJson();

        $this->get('/status/status');

        $this->assertResponseOk();
        $this->assertNoRedirect();

        $expected = json_encode(['lastShoutId' => 4]);
        $this->assertResponseContains($expected);
    }
}
