<?php

namespace App\Test\TestCase\Lib\Model;

use App\Lib\Model\Table\FieldFilter;
use Cake\TestSuite\TestCase;

class AppTableTest extends TestCase
{
    /** @var FieldFilter */
    private $fieldFilter;

    public function setUp()
    {
        parent::setUp();
        $this->fieldFilter = new FieldFilter();
    }

    public function tearDown()
    {
        unset($this->fieldFilter);
        parent::tearDown();
    }

    public function testFilterFields()
    {
        $filter = ['a', 'b'];
        $this->fieldFilter->setConfig('test', $filter);

        $data = ['a' => 1, 'b' => 2, 'c' => 3];
        $result = $this->fieldFilter->filterFields($data, 'test');

        $expected = ['a' => 1, 'b' => 2];
        $this->assertEquals($expected, $result);
    }

    public function testFilterFieldsClassPreset()
    {
        $filter = ['a', 'c'];
        $this->fieldFilter->setConfig('test', $filter);

        $data = ['a' => 1, 'b' => 2, 'c' => 3];
        $result = $this->fieldFilter->filterFields($data, 'test');

        $expected = ['a' => 1, 'c' => 3];
        $this->assertEquals($expected, $result);
    }

    public function testRequiredFields()
    {
        $required = ['a', 'b'];
        $this->fieldFilter->setConfig('test', $required);

        $data = ['a' => 1, 'b' => 2, 'c' => 3];
        $result = $this->fieldFilter->requireFields($data, 'test');
        $this->assertTrue($result);

        $required = ['a', 'b', 'd'];
        $this->fieldFilter->setConfig('test', $required);

        $data = ['a' => 1, 'b' => 2, 'c' => 3];
        $result = $this->fieldFilter->requireFields($data, 'test');
        $this->assertFalse($result);
    }
}
