<?php

namespace App\Test\TestCase\Model\Table;

use App\Lib\Model\Table\AppTable;
use Saito\Test\Model\Table\SaitoTableTestCase;

class AppTableTest extends SaitoTableTestCase
{

    public $tableClass = AppTable::class;

    public $fixtures = ['app.category', 'app.entry', 'app.user'];

    public function testFilterFields()
    {
        $data = ['a' => 1, 'b' => 2, 'c' => 3];
        $filter = ['a', 'b'];
        $this->Table->filterFields($data, $filter);
        $expected = ['a' => 1, 'b' => 2];
        $this->assertEquals($expected, $data);
    }

    public function testFilterFieldsClassPreset()
    {
        $data = ['a' => 1, 'b' => 2, 'c' => 3];
        $this->Table->filterFields($data, ['a', 'c']);

        $expected = ['a' => 1, 'c' => 3];
        $this->assertEquals($expected, $data);
    }

    public function testRequiredFields()
    {
        $data = ['a' => 1, 'b' => 2, 'c' => 3];
        $required = ['a', 'b'];
        $result = $this->Table->requireFields($data, $required);
        $this->assertTrue($result);

        $data = ['a' => 1, 'b' => 2, 'c' => 3];
        $required = ['a', 'b', 'd'];
        $result = $this->Table->requireFields($data, $required);
        $this->assertFalse($result);
    }
}
