<?php

declare(strict_types=1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace Feeds\Test\TestCase\Controller;

use Cake\ORM\TableRegistry;
use Saito\Test\IntegrationTestCase;

class PostingsControllerTest extends IntegrationTestCase
{
    public $fixtures = [
        'app.Category',
        'app.Entry',
        'app.Setting',
        'app.Smiley',
        'app.SmileyCode',
        'app.User',
        'app.UserBlock',
        'app.UserIgnore',
        'app.UserOnline',
        'app.UserRead'
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
        $this->assertResponseContains('<dc:creator xmlns:dc="http://purl.org/dc/elements/1.1/">Alice</dc:creator>');
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
