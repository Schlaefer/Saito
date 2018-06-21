<?php

declare(strict_types = 1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers 2018
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace Installer\Test\TestCase\Controller;

use Cake\Core\Configure;
use Cake\Datasource\ConnectionManager;
use Cake\Filesystem\File;
use Cake\ORM\TableRegistry;
use Installer\Lib\DbVersion;
use Installer\Lib\IntegrationTestCase;

class InstallerControllerTest extends IntegrationTestCase
{
    protected $isUpdated = false;

    public function setUp()
    {
        parent::setUp();
        $this->dropTables();
        $this->createInstallerToken();

        Configure::write('Saito.installed', false);
        Configure::write('Saito.updated', false);
    }

    public function tearDown()
    {
        $this->createInstallerToken();
        $this->dropTables();
        parent::tearDown();
    }

    public function testInstallerConnectionIsMade()
    {
        Configure::write('Saito.installed', false);

        $this->get('/');

        $this->assertResponseOk();
        $this->assertResponseContains('Saito Installation');

        $this->assertTrue($this->viewVariable('database'));
        $this->assertFalse($this->viewVariable('tables'));
    }

    public function testInstallerCreateTablesOnEmptyDb()
    {
        Configure::write('Saito.installed', false);

        $this->post('/', ['username' => 'admin', 'password' => 'admin']);

        $this->assertResponseOk();

        $this->assertTrue($this->viewVariable('database'));
        $this->assertTrue($this->viewVariable('tables'));

        $dbVersion = (new DbVersion(TableRegistry::get('Settings')))->get();
        $this->assertEquals(Configure::read('Saito.v'), $dbVersion);

        $Users = TableRegistry::get('Users');
        $admin = $Users->get(1);
        $this->assertEquals('21232f297a57a5a743894a0e4a801fc3', $admin->get('password'));
    }

    public function testInstallerWithExistingDb()
    {
        Configure::write('Saito.installed', false);
        $this->createSettings();
        (new DbVersion(TableRegistry::get('Settings')))->set('4.10.0');

        $token = new File(CONFIG . 'installer');
        $this->assertTrue($token->exists());

        $this->get('/');

        $this->assertFalse($token->exists());
        $this->assertRedirect('/');
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
    }
}
