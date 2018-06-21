<?php

declare(strict_types = 1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers 2018
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace Installer\Controller;

use Cake\Core\Configure;
use Cake\Datasource\ConnectionManager;
use Cake\Datasource\ConnectionRegistry;
use Cake\Event\Event;
use Cake\Filesystem\File;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Installer\Lib\DbVersion;
use Psr\Log\LogLevel;

/**
 * Install Controller
 */
class InstallController extends AppController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|void
     */
    public function start()
    {
        $this->log('Start installer.', LogLevel::INFO, ['saito.install']);

        $database = $tables = false;
        try {
            $connection = ConnectionManager::get('default');
            $connected = $connection->connect();
            $database = true;
        } catch (\Throwable $connectionError) {
        }

        $this->set(compact('database', 'tables'));

        if (!$database) {
            $this->log('No database connection.', LogLevel::INFO, ['saito.install']);

            return;
        }
        $this->log('Database connection found.', LogLevel::INFO, ['saito.install']);

        $foundDbVersion = null;
        $hasSettingsTable = false;
        try {
            $foundDbVersion = (new DbVersion($this->loadModel('Settings')))->get();
            $hasSettingsTable = true;
        } catch (\Throwable $e) {
        }

        if ($hasSettingsTable) {
            $this->log('Installer found Settings-table. Moving on to Updater.', LogLevel::INFO, ['saito.install']);
            //// disable installer and move on to updater instead
            $token = (new File(CONFIG . 'installer'))
                ->delete();

            return $this->redirect('/');
        }

        $this->log('Installer checking migration status.', LogLevel::INFO, ['saito.install']);

        $status = $this->migrations->status();
        if (empty($status[0]) || empty($status[0]['status']) || $status[0]['status'] !== 'down') {
            $this->log('Installer migration has run.', LogLevel::INFO, ['saito.install']);
            $tables = true;
            $this->set(compact('database', 'tables'));

            return;
        }

        $data = $this->request->getData();
        if (empty($data)) {
            $this->set(compact('database', 'tables'));

            return;
        }

        $this->log('Installer starting migrate.', LogLevel::INFO, ['saito.install']);
        $this->migrations->migrate();
        $this->log('Installer starting seed.', LogLevel::INFO, ['saito.install']);
        $this->migrations->seed();

        $this->log('Installer setting admin user.', LogLevel::INFO, ['saito.install']);
        $Users = TableRegistry::getTableLocator()->get('Users', ['className' => Table::class]);
        $admin = $Users->get(1);
        $data['password'] = md5($data['password']);
        $Users->patchEntity($admin, $data);
        if (!$Users->save($admin)) {
            return;
        }

        $this->log('Marking installed.', LogLevel::INFO, ['saito.install']);
        (new DbVersion($this->loadModel('Settings')))->set(Configure::read('Saito.v'));

        $tables = true;

        $this->log('Installer finished.', LogLevel::INFO, ['saito.install']);
        $this->set(compact('database', 'tables'));
    }
}
