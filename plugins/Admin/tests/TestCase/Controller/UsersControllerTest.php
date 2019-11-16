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
        'app.Setting',
        'app.User',
        'app.UserBlock',
        'app.UserRead',
        'app.UserOnline',
    ];

    public function setUp()
    {
        parent::setUp();
        foreach (['Users'] as $table) {
            $this->$table = TableRegistry::get($table);
        }
    }

    public function testUsersIndexAccess()
    {
        $this->assertRouteForRole('/admin/users/block', 'admin');
    }
}
