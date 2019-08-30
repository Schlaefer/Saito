<?php

declare(strict_types=1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace App\Test\TestCase\Controller\Admin;

use Cake\ORM\TableRegistry;
use Saito\Test\IntegrationTestCase;

/**
 * Class CategoriesControllerTest
 *
 * @package App\Test\TestCase\Controller\Admin
 * @group App\Test\TestCase\Controller\Admin
 */
class CategoriesControllerTest extends IntegrationTestCase
{

    public $fixtures = [
        'app.Category',
        'app.Entry',
        'app.Setting',
        'app.User',
        'app.UserBlock',
        'app.UserIgnore',
        'app.UserRead',
        'app.UserOnline'
    ];

    public function setUp()
    {
        parent::setUp();
        foreach (['Entries', 'Categories'] as $table) {
            $this->$table = TableRegistry::get($table);
        }
    }

    /**
     * Tests viewing empty "add new category" form
     */
    public function testAddGet()
    {
        $this->mockSecurity();
        $this->_loginUser(1);

        $this->get('/admin/categories/add');

        $this->assertResponseOk();

        $category = $this->viewVariable('category');

        // default accession for new categories should be set to logged-in user (1)
        $this->assertEquals(1, $category->get('accession'));
        $this->assertEquals(1, $category->get('accession_new_thread'));
        $this->assertEquals(1, $category->get('accession_new_posting'));
    }

    /**
     * delete category and postings
     */
    public function testDeleteDelete()
    {
        $this->mockSecurity();
        $this->_loginUser(1);
        $source = 2;

        $readPostings = function () use ($source) {
            $read = [];
            $read['all'] = $this->Entries->find()->all()->count();
            $read['source'] = $this->Entries->find()
                ->where(['category_id' => $source])
                ->count();

            return $read;
        };

        $this->assertTrue($this->Categories->exists($source));
        $before = $readPostings();
        $this->assertGreaterThan(0, $before['source']);

        $data = ['mode' => 'delete'];
        $this->post('/admin/categories/delete/2', $data);

        $this->assertFalse($this->Categories->exists($source));
        $this->assertRedirect('/admin/categories');

        $after = $readPostings();
        $this->assertEquals(0, $after['source']);
        $expected = $before['all'] - $before['source'];
        $this->assertEquals($expected, $after['all']);
    }

    /**
     * delete category and merge postings into other category
     */
    public function testDeleteMerge()
    {
        $this->mockSecurity();
        $this->_loginUser(1);
        $source = 2;
        $target = 4;

        $readPostings = function () use ($source, $target) {
            $read = [];
            $read['all'] = $this->Entries->find()->all()->count();
            $read['source'] = $this->Entries->find()
                ->where(['category_id' => $source])
                ->count();
            $read['target'] = $this->Entries->find()
                ->where(['category_id' => $target])
                ->count();

            return $read;
        };

        $this->assertTrue($this->Categories->exists($source));
        $this->assertTrue($this->Categories->exists($target));
        $before = $readPostings();
        $this->assertGreaterThan(0, $before['source']);
        $this->assertGreaterThan(0, $before['target']);

        $data = ['mode' => 'move', 'targetCategory' => $target];
        $this->post('/admin/categories/delete/2', $data);

        // old category removed
        $this->assertFalse($this->Categories->exists($source));
        // target category not eaten
        $this->assertTrue($this->Categories->exists($target));

        $after = $readPostings();
        // no posting in old category
        $this->assertEquals(0, $after['source']);
        // postings are moved to new category
        $expected = $before['target'] + $before['source'];
        $this->assertEquals($expected, $after['target']);
        // no post is lost
        $this->assertEquals($before['all'], $after['all']);

        $this->assertRedirect('/admin/categories');
    }
}
