<?php

namespace App\Test\TestCase\Model\Table;

use Saito\App\Registry;
use Saito\Test\Model\Table\SaitoTableTestCase;

class CategoriesTableTest extends SaitoTableTestCase
{

    public $tableClass = 'Categories';

    public $fixtures = [
        'app.Category',
        'app.Entry',
        'app.User',
        'app.UserOnline',
    ];

    public function testUpdateEvent()
    {
        $this->Table = $this->getMockForModel(
            'Categories',
            ['dispatchDbEvent'],
            ['table' => 'categories']
        );
        $this->Table->expects($this->once())
            ->method('dispatchDbEvent')
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
            ['dispatchDbEvent'],
            ['table' => 'categories']
        );
        $this->Table->expects($this->once())
            ->method('dispatchDbEvent')
            ->with('Cmd.Cache.clear', ['cache' => ['Saito', 'Thread']]);

        $category = $this->Table->get(1);
        $this->Table->delete($category);
    }

    public function testValidateRoleExists()
    {
        $roleId = 42;
        Registry::get('Permissions')->getRoles()->add('fooRole', $roleId);

        /// Success
        $data = [
            'category_order' => 0,
            'accession' => (string)$roleId,
            'accession_new_thread' => (string)$roleId,
            'accession_new_posting' => (string)$roleId,
        ];
        $entity = $this->Table->newEntity($data);
        $this->assertEmpty($entity->getErrors());

        /// Failure
        $data = [
            'accession' => '999',
            'accession_new_thread' => '999',
            'accession_new_posting' => '999',
        ];
        $entity = $this->Table->newEntity($data);
        $errors = $entity->getErrors();
        $this->assertArrayHasKey('validateRoleExists', $entity->getError('accession'));
        $this->assertArrayHasKey('validateRoleExists', $entity->getError('accession'));
        $this->assertArrayHasKey('validateRoleExists', $entity->getError('accession'));
    }
}
