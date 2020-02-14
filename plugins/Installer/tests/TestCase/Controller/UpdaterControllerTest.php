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
use Installer\Lib\IntegrationTestCase;
use Migrations\Migrations;

class UpdaterControllerTest extends IntegrationTestCase
{
    public $fixtures = ['app.Setting', 'plugin.Installer.Phinxlog'];

    /**
     * @var File
     */
    protected $token;

    /**
     * @var DbVersion
     */
    protected $dbVersion;

    public function setUp(): void
    {
        parent::setUp();
        $this->token = new File(CONFIG . 'updater');
        $this->dbVersion = (new DbVersion(TableRegistry::get('Settings')));
        Configure::write('Saito.updated', false);
    }

    public function tearDown(): void
    {
        $this->token->delete();
        unset($this->settings, $this->token);
        parent::tearDown();
    }

    public function testUpdaterShowFailureAfterAbortedUpdated()
    {
        $this->token->write('');
        $this->get('/');

        $this->assertResponseOk();
        $this->assertEquals('failure', $this->_controller->viewBuilder()->getTemplate());
        $this->assertResponseContains((string)1529737182);
    }

    public function testUpdaterShowFailureNoDbVersionString()
    {
        $this->dbVersion->set(null);
        $this->get('/');

        $this->assertResponseOk();
        $this->assertEquals('failure', $this->_controller->viewBuilder()->getTemplate());
        $this->assertResponseContains((string)1529737397);
    }

    public function testUpdaterShowFailureWrongDbVersionString()
    {
        $this->dbVersion->set('4.9.99');
        $this->get('/');

        $this->assertResponseOk();
        $this->assertEquals('failure', $this->_controller->viewBuilder()->getTemplate());
        $this->assertResponseContains((string)1529737648);
    }

    public function testUpdaterInitMigrationsFormEmpty()
    {
        $this->dropTables();
        $migration = new Migrations(['connection' => 'test']);
        $migration->migrate(['target' => '20180620081553']);
        $migration->seed(['seed' => 'SettingsSeed']);
        $this->dbVersion->set('4.10.0');
        $connection = ConnectionManager::get('test');
        $connection->execute('DROP TABLE `phinxlog`;');

        $this->post('/');

        $this->assertResponseOk();
        $this->assertEquals('start', $this->_controller->viewBuilder()->getTemplate());

        $this->assertTrue($this->viewVariable('startAuthError'));

        $status = $migration->status();
        $this->assertEquals('down', array_pop($status)['status']);
    }

    public function testUpdaterInitMigrationsFailureWrongPassword()
    {
        $this->dropTables();
        $migration = new Migrations(['connection' => 'test']);
        $migration->migrate(['target' => '20180620081553']);
        $migration->seed(['seed' => 'SettingsSeed']);
        $this->dbVersion->set('4.10.0');
        $connection = ConnectionManager::get('test');
        $connection->execute('DROP TABLE `phinxlog`;');

        $connection = ConnectionManager::get('default');
        $config = $connection->config();

        $this->mockSecurity();
        $this->post('/', ['dbname' => $config['database'], 'dbpassword' => 'foobar']);

        $this->assertResponseOk();
        $this->assertEquals('start', $this->_controller->viewBuilder()->getTemplate());

        $this->assertTrue($this->viewVariable('startAuthError'));

        $status = $migration->status();
        $this->assertEquals('down', array_pop($status)['status']);
    }

    public function testUpdaterInitMigrationsSuccess()
    {
        $this->dropTables();
        $migration = new Migrations(['connection' => 'test']);
        $migration->migrate(['target' => '20180620081553']);
        $migration->seed(['seed' => 'SettingsSeed']);
        $this->dbVersion->set('4.10.0');
        $connection = ConnectionManager::get('test');
        $connection->execute('DROP TABLE `phinxlog`;');

        $connection = ConnectionManager::get('default');
        $config = $connection->config();

        $this->mockSecurity();
        $this->post('/', ['dbname' => $config['database'], 'dbpassword' => $config['password']]);

        $this->assertResponseOk();
        $this->assertEquals('success', $this->_controller->viewBuilder()->getTemplate());

        $status = $migration->status();
        $this->assertEquals('up', array_pop($status)['status']);
    }
}
