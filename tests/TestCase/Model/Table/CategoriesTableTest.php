<?php

namespace App\Test\TestCase\Model\Table;

use Cake\Core\Configure;
use Saito\Test\Model\Table\SaitoTableTestCase;

class CategoriesTableTest extends SaitoTableTestCase
{

    public $tableClass = 'Categories';

    public $fixtures = [
        'app.Category',
        'app.Entry',
        'app.User',
        'app.UserOnline'
    ];

    public function testUpdateEvent()
    {
        $this->Table = $this->getMockForModel(
            'Categories',
            ['_dispatchEvent'],
            ['table' => 'categories']
        );
        $this->Table->expects($this->once())
            ->method('_dispatchEvent')
            ->with('Cmd.Cache.clear', ['cache' => ['Saito', 'Thread']]);

        $data = ['category' => 'foo'];

        $category = $this->Table->get(1);
        $this->Table->patchEntity($category, $data);
        $this->Table->save($category);
    }

    public function testDeleteEvent()
    {
        $this->Table = $this->getMockForModel(
            'Categories',
            ['_dispatchEvent'],
            ['table' => 'categories']
        );
        $this->Table->expects($this->once())
            ->method('_dispatchEvent')
            ->with('Cmd.Cache.clear', ['cache' => ['Saito', 'Thread']]);

        $category = $this->Table->get(1);
        $this->Table->delete($category);
    }
}
