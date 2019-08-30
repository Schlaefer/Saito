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
class UsersControllerTest extends IntegrationTestCase
{

    public $fixtures = [
        'app.Category',
        'app.Entry',
        'app.Setting',
        'app.User',
        'app.UserBlock',
        'app.UserIgnore',
        'app.UserRead',
        'app.UserOnline',
        'plugin.Bookmarks.Bookmark',
        'plugin.ImageUploader.Uploads',
    ];

    public function setUp()
    {
        parent::setUp();
        foreach (['Users'] as $table) {
            $this->$table = TableRegistry::get($table);
        }
    }

    public function testUsersBlockIndex()
    {
        $this->_loginUser(1);

        $this->get('/admin/users/block');

        $this->assertResponseOk();
    }

    public function testDelete()
    {
        $this->mockSecurity();

        /*
         *  not logged in can't delete
         */
        $url = '/admin/users/delete/3';
        $this->get($url);
        $this->assertRedirectLogin($url);
        $this->assertTrue($this->_controller->Users->exists(3));

        /*
         * user can't delete admin/users
         */
        $url = '/admin/users/delete/4';
        $this->_loginUser(3);
        $this->get($url);
        $this->assertTrue($this->_controller->Users->exists(4));
        $this->assertRedirectLogin($url);

        /*
         *  mod can access delete ui
         */
        $this->_loginUser(2);
        $this->get('/admin/users/delete/4');
        $this->assertNoRedirect();

        /*
         *  admin can access delete ui
         */
        $this->_loginUser(6);
        $this->get('/admin/users/delete/4');
        $this->assertNoRedirect();

        /*
         * you can't delete non existing users
         */
        $countBeforeDelete = $this->_controller->Users->find('all')->count();
        $data = ['modeDelete' => 1];
        $this->_loginUser(6);
        $this->post('/admin/users/delete/9999', $data);
        $countAfterDelete = $this->_controller->Users->find('all')->count();
        $this->assertEquals($countBeforeDelete, $countAfterDelete);
        $this->assertRedirect('/');

        /*
         * you can't delete yourself
         */
        $data = ['modeDelete' => 1];
        $this->_loginUser(6);
        $this->post('/admin/users/delete/6', $data);
        $this->assertTrue($this->_controller->Users->exists(6));

        /*
         * you can't delete the root user
         */
        $this->_loginUser(6);
        $this->post('/admin/users/delete/1', $data);
        $this->assertTrue($this->_controller->Users->exists(1));

        /*
         *  mods can't delete admin
         */
        $this->_loginUser(2);
        $this->post('/admin/users/delete/6', $data);
        $this->assertTrue($this->_controller->Users->exists(6));
    }

    public function testDeleteAdminDeletesUserSuccess()
    {
        $this->mockSecurity();
        $this->_loginUser(6);
        $data = ['modeDelete' => 1];

        $this->post('/admin/users/delete/5', $data);

        $this->assertFalse($this->_controller->Users->exists(5));
        $this->assertRedirect('/');
    }
}
