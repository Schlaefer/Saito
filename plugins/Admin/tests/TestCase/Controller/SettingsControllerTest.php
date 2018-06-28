<?php

declare(strict_types = 1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers 2018
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
        'app.category',
        'app.entry',
        'app.setting',
        'app.user',
        'app.user_block',
        'app.user_ignore',
        'app.user_read',
        'app.useronline'
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
