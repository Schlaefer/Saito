<?php
declare(strict_types=1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace Installer\Test\TestCase\Controller;

use Cake\Core\Configure;
use Cake\Datasource\ConnectionManager;
use Cake\Filesystem\File;
use Cake\ORM\TableRegistry;
use Installer\Lib\DbVersion;
use Installer\Lib\InstallerState;
use Installer\Lib\IntegrationTestCase;

class InstallControllerTest extends IntegrationTestCase
{
    protected $isUpdated = false;

    public function setUp(): void
    {
        parent::setUp();
        $this->dropTables();
        $this->createInstallerToken();
        InstallerState::reset();

        Configure::write('Saito.installed', false);
        Configure::write('Saito.updated', false);
    }

    public function tearDown(): void
    {
        $this->createInstallerToken();
        $this->dropTables();
        InstallerState::reset();
        parent::tearDown();
    }

    public function testIndex()
    {
        $this->get('/');

        $this->assertRedirect('install/dbconnection');
    }

    public function testWrongInstallerState()
    {
        $actions = [
            'salt',
            'connected',
            'migrate',
            'data',
            'finished',
        ];

        foreach ($actions as $action) {
            $this->get('install/' . $action);
            $this->assertRedirect('/');
        }
    }

    public function testMigrateAndData()
    {
        InstallerState::set('migrate');
        $this->post('install/migrate');

        $email = 'test@example.com';
        $this->post(
            'install/data',
            [
                'username' => 'admin',
                'password' => 'admin',
                'password_confirm' => 'admin',
                'user_email' => $email,
            ]
        );

        $this->assertRedirect('install/finished');

        $Settings = TableRegistry::getTableLocator()->get('Settings');
        $this->assertEquals($email, $Settings->findByName('forum_email')->first()->get('value'));

        $dbVersion = (new DbVersion($Settings))->get();
        $this->assertEquals(Configure::read('Saito.v'), $dbVersion);

        $Users = TableRegistry::getTableLocator()->get('Users');
        $admin = $Users->get(1);
        $this->assertTextContains('$2y$', $admin->get('password'));
        $this->assertEquals($email, $admin->get('user_email'));
    }

    public function testConnectedDbExists()
    {
        InstallerState::set('connected');
        $this->createSettings();
        (new DbVersion(TableRegistry::get('Settings')))->set('4.10.0');

        $token = new File(CONFIG . 'installer');
        $this->assertTrue($token->exists());

        $this->get('install/connected');

        $this->assertResponseCode(200);
    }

    private function createInstallerToken()
    {
        (new File(CONFIG . 'installer'))->create();
    }

    private function createSettings()
    {
        $connection = ConnectionManager::get('test');
        $connection->execute('DROP TABLE IF EXISTS `settings`;');
        $connection->execute('CREATE TABLE `settings` (id INT(11) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT);');
        $connection->execute('ALTER TABLE settings ADD `name` VARCHAR(100)  NULL  DEFAULT NULL  AFTER `id`;');
        $connection->execute('ALTER TABLE settings ADD `value` VARCHAR(100)  NULL  DEFAULT NULL  AFTER `id`;');
        $connection->execute("INSERT INTO `settings` (`id`, `name`, `value`) VALUES ('1', 'db_version', NULL);");
        $connection->execute("INSERT INTO `settings` (`id`, `name`, `value`) VALUES ('2', 'forum_email', NULL);");
    }
}
