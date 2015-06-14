<?php

namespace Saito\Test\Posting;

use Cake\ORM\TableRegistry;
use Saito\App\Registry;
use Saito\Test\SaitoTestCase;

class PostingTest extends SaitoTestCase
{

    public $Table;

    public $fixtures = [
        'app.entry',
        'app.category',
        'app.user',
    ];

    public function setUp()
    {
        parent::setUp();
        $this->Table = TableRegistry::get('Entries');
    }

    public function tearDown()
    {
        unset($this->Table);
        parent::tearDown();
    }

    public function testGetAllChildren()
    {
        $posting = $this->Table->treesForThreads([1])[1];

        $expected = [2, 3, 7, 8, 9];
        $result = $posting->getAllChildren();
        $actual = array_keys($result);
        sort($actual);
        $this->assertEquals($actual, $expected);

        $expected = [3, 7, 9];
        $result = $posting->getThread()->get(2)->getAllChildren();
        $actual = array_keys($result);
        sort($actual);
        $this->assertEquals($actual, $expected);
    }
}
