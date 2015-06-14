<?php

namespace App\Test\TestCase\Model\Table;

use Cake\Core\Configure;
use Saito\Test\Model\Table\SaitoTableTestCase;

class ShoutsTableTest extends SaitoTableTestCase
{

    public $tableClass = 'Shouts';

    public $fixtures = ['app.shout', 'app.user'];

    public function setUp()
    {
        parent::setUp();
    }

    public function testPush()
    {
        $this->Table = $this->getMockForModel('Shouts', ['prepareMarkup']);
        $this->Table->expects($this->once())
            ->method('prepareMarkup')
            ->will($this->returnArgument(0));
        $this->Table->table('shouts');

        $_numberOfShouts = $this->Table->find()->count();

        $data = [
            'text' => 'The text',
            'user_id' => 3
        ];
        $this->Table->push($data);

        $result = $this->Table->get($_numberOfShouts + 1)->first();

        $this->assertGreaterThan(time() - 60, strtotime($result->get('time')));

        $result = array_intersect_key($result->toArray(), $data);
        $this->assertEquals($data, $result);

        $result = $this->Table->find()->count();
        $expected = $_numberOfShouts + 1;
        $this->assertEquals($result, $expected);
    }

    public function testNoRotate()
    {
        $this->Table = $this->getMockForModel(
            'Shouts',
            ['shift', 'prepareMarkup']
        );
        $this->Table->expects($this->once())
            ->method('prepareMarkup')
            ->will($this->returnArgument(0));
        $this->Table->table('shouts');

        $data = ['text' => 'The text', 'user_id' => 3];

        $_numberOfShouts = $this->Table->find()->count();
        $this->assertGreaterThanOrEqual(3, $_numberOfShouts);

        $this->Table->maxNumberOfShouts = $_numberOfShouts + 1;

        $this->Table->expects($this->never())->method('shift');

        $this->Table->push($data);
    }

    public function testRotate()
    {
        $this->Table = $this->getMockForModel(
            'Shouts',
            ['shift', 'prepareMarkup']
        );
        $this->Table->expects($this->once())
            ->method('prepareMarkup')
            ->will($this->returnArgument(0));
        $this->Table->table('shouts');

        $data = ['text' => 'The text', 'user_id' => 3];

        $_numberOfShouts = $this->Table->find()->count();
        $this->assertGreaterThanOrEqual(3, $_numberOfShouts);

        $this->Table->maxNumberOfShouts = $_numberOfShouts - 1;

        $this->Table->expects($this->exactly(2))
            ->method('shift')
            ->will($this->returnValue(true));
        $this->Table->push($data);
    }

    public function testShift()
    {
        $before = $this->Table->find()
            ->select(['id'])
            ->order(['id' => 'ASC'])
            ->all()
            ->toArray();
        $result = $this->Table->shift();
        $this->assertTrue($result);

        $after = $this->Table->find()
            ->select(['id'])
            ->order(['id' => 'ASC'])
            ->all()
            ->toArray();

        array_shift($before);

        $this->assertEquals($after, $before);
    }
}
