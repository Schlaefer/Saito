<?php

namespace App\Test\TestCase\Model\Behavior;

use App\Model\Behavior\MarkupBehavior;
use Cake\TestSuite\TestCase;
use Saito\Markup\Settings;

class MarkupBehaviorTest extends TestCase
{

    public function setUp()
    {
        parent::setUp();
        $table = $this->getMock('Cake\ORM\Table');
        $this->Markup = new MarkupBehavior($table);
        new Settings(
            [
                'server' => 'http://example.com',
                'webroot' => '/foo/',
                'hashBaseUrl' => 'hash/base/'
            ]
        );
    }

    public function tearDown()
    {
        unset($this->MarkupBehavior);
        parent::tearDown();
    }

    /**
     * Test hashing of internal view links as string
     *
     * @return void
     */
    public function testPrepareInputHashString()
    {
        $input = 'http://example.com/foo/hash/base/345';
        $result = $this->Markup->prepareMarkup($input);
        $expected = "#345";
        $this->assertEquals($result, $expected);

        $input = '[url=http://example.com/foo/hash/base/345]foo[/url]';
        $result = $this->Markup->prepareMarkup($input);
        $expected = $input;
        $this->assertEquals($result, $expected);
    }
}
