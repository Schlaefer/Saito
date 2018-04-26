<?php

namespace Feeds\Test\TestCase\Controller;

use Cake\ORM\TableRegistry;
use Saito\Test\IntegrationTestCase;

class ArticlesControllerTest extends IntegrationTestCase
{
    public $fixtures = [
        'app.category',
        'app.entry',
        'app.setting',
        'app.smiley',
        'app.smiley_code',
        'app.user',
        'app.user_block',
        'app.user_ignore',
        'app.user_online',
        'app.user_read'
    ];

    public function testNew()
    {
        $this->get('/feeds/postings/new.rss');
        $result = $this->viewVariable('entries');
        $first = $result->first();
        $this->assertEquals($first->get('subject'), 'First_Subject');
        $this->assertNull($first->get('password'));

        $this->assertResponseOk();
        $this->assertResponseContains('<title><![CDATA[First_Subject]]></title>');
    }

    public function testThreads()
    {
        $this->get('/feeds/postings/threads.rss');
        $result = $this->viewVariable('entries');
        $first = $result->first();
        $this->assertEquals($first->get('subject'), 'First_Subject');
        $this->assertNull($first->get('password'));

        $this->assertResponseOk();
        $this->assertResponseContains('<title><![CDATA[First_Subject]]></title>');
    }
}
