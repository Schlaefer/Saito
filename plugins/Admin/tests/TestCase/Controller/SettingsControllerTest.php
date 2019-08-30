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
 * Class SettingsControllerTest
 */
class SettingsControllerTest extends IntegrationTestCase
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

    public function testIndexFailureUserNotLoggedIn()
    {
        $this->get('/admin/settings/index');

        $this->assertRedirectLogin('/admin/settings/index');
    }

    public function testIndexFailureUserNoAdmin()
    {
        $this->_loginUser(2);
        $this->get('/admin/settings/index');

        $this->assertRedirectLogin('/admin/settings/index');
    }

    public function testIndexSuccess()
    {
        $this->_loginUser(1);
        $this->get('/admin/settings/index');

        $this->assertResponseOk();
    }
}
