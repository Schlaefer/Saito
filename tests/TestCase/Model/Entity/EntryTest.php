<?php

namespace App\Test\TestCase\Model\Entity;

use App\Model\Entity\Entry;
use Cake\ORM\TableRegistry;
use Plugin\BbcodeParser\src\Lib\Markup;
use Saito\App\Registry;
use Saito\Markup\Settings;
use Saito\Test\SaitoTestCase;

class EntryTest extends SaitoTestCase
{
    public $fixtures = ['app.Category', 'app.Entry', 'app.User'];

    public function testIsRoot()
    {
        $postings = TableRegistry::get('Entries');

        $posting = $postings->get(8);
        $this->assertFalse($posting->isRoot());

        $posting = $postings->get(4);
        $this->assertTrue($posting->isRoot());
    }

    /**
     * Test hashing of internal view links as string
     *
     * @return void
     */
    public function testTextMutatorMarkupPreprocessor()
    {
        $markupSettings = Registry::get('MarkupSettings');
        $markupSettings->set([
            'server' => 'http://example.com',
            'webroot' => '/foo/',
            'hashBaseUrl' => 'hash/base/',
        ]);

        $input = 'http://example.com/foo/hash/base/345';
        $entity = (new Entry())->set('text', $input);
        $result = $entity->get('text');
        $expected = "#345";
        $this->assertEquals($result, $expected);

        $input = '[url=http://example.com/foo/hash/base/345]foo[/url]';
        $entity = (new Entry())->set('text', $input);
        $result = $entity->get('text');
        $expected = $input;
        $this->assertEquals($result, $expected);
    }
}
