<?php

namespace App\Test\TestCase\Model\Table;

use Saito\Test\Model\Table\SaitoTableTestCase;

class UserBlocksTableTest extends SaitoTableTestCase
{

    public $tableClass = 'UserBlocks';

    public $fixtures = [
        'app.User',
        'app.UserBlock',
    ];

    public function testFindToGc()
    {
        $count = $this->Table->find('toGc')->count();
        $this->assertEquals(1, $count);
    }

    public function testGc()
    {
        $before = $this->Table->find('toGc');
        $this->assertEquals(1, $before->count());

        $this->Table->gc();

        $after = $this->Table->find('toGc');
        $this->assertEquals(0, $after->count());
    }
}
