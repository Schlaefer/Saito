<?php

declare(strict_types=1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace Installer\Controller;

use Cake\Core\Configure;
use Cake\Database\Connection;
use Cake\Datasource\ConnectionManager;
use Cake\Http\Response;
use Cake\ORM\TableRegistry;
use Cake\Utility\Security;
use Installer\Lib\DbVersion;
use Installer\Lib\InstallerState;
use Psr\Log\LogLevel;

/**
 * Install Controller
 */
class InstallController extends AppController
{
    /**
     * {@inheritdoc}
     */
    public function initialize(): void
    {
        parent::initialize();
        $this->loadComponent('Referer');
    }

    /**
     * {@inheritdoc}
     */
    public function beforeFilter(\Cake\Event\EventInterface $event)
    {
        $this->set('titleForLayout', __d('installer', 'title'));
    }

    /**
     * Starting point
     *
     * @return \Cake\Http\Response|void
     */
    public function index()
    {
        $this->log('Start installer.');
        InstallerState::reset();

        return $this->redirect('/install/dbconnection');
    }

    /**
     * Check if connection to DB is working
     *
     * @return \Cake\Http\Response|void
     */
    public function dbconnection()
    {
        try {
            /** @var Connection */
            $connection = ConnectionManager::get('default');
            if ($connection->connect()) {
                $this->log('Database connection found.');

                return $this->installerRedirect('salt');
            }
        } catch (\Throwable $connectionError) {
            // connection manager will throw error if no connection
        }

        $this->log('No database connection.');
        $this->set('database', false);
    }

    /**
     * Check if security salt is set
     *
     * @return \Cake\Http\Response|void
     */
    public function salt()
    {
        if (!InstallerState::check('salt')) {
            return $this->redirect('/');
        }

        $secured = (Security::getSalt() !== '__SALT__')
            && (Configure::read('Security.cookieSalt') !== '__SALT__');

        if ($secured) {
            $this->log('Security salt is set.');

            return $this->installerRedirect('connected');
        }

        $this->log('Security salt is not set.');
        $this->set('secured', $secured);
    }

    /**
     * Check if there's an existing installation
     *
     * User uses new software version but hit the Installer accidentally.
     *
     * @return \Cake\Http\Response|void
     */
    public function connected()
    {
        if (!InstallerState::check('connected')) {
            return $this->redirect('/');
        }

        try {
            (new DbVersion($this->loadModel('Settings')))->get();
            $this->log('Installer found Settings-table.');

            return;
        } catch (\Throwable $e) {
            // Settings-table doesn't exist yet
        }

        $this->log('Installer didn\'t find Settings-table.');

        return $this->installerRedirect('migrate');
    }

    /**
     * Insert tables and seed data
     *
     * @return \Cake\Http\Response|void
     */
    public function migrate()
    {
        if (!InstallerState::check('migrate')) {
            return $this->redirect('/');
        }

        $this->log('Installer checking migration status.');

        if ($this->getRequest()->is('post')) {
            $this->set('tables', false);

            $this->log('Installer starting initial migrate.');
            // Initial layout
            $this->migrations->migrate(['target' => 'Saitox5x0x0']);
            $this->log('Installer starting seed.');
            // The seed is meant for the initial layout
            $this->migrations->seed();
            $this->log('Installer starting follow-up migrate.');
            // Apply migration changes which applies to DB and seed-data (esp. settings-table)
            $this->migrations->migrate();
        }

        $status = $this->migrations->status();
        if (empty($status[0]) || empty($status[0]['status']) || $status[0]['status'] !== 'down') {
            $this->log('Installer migration has run.');

            return $this->installerRedirect('data');
        }
    }

    /**
     * Insert data from user-input
     *
     * @return \Cake\Http\Response|void
     */
    public function data()
    {
        if (!InstallerState::check('data')) {
            return $this->redirect('/');
        }

        $Users = TableRegistry::getTableLocator()->get('Users');

        if ($this->getRequest()->is('get')) {
            $this->set('admin', $Users->newEntity());

            return;
        }

        /// setting admin user
        $this->log('Installer setting admin user.');
        $admin = $Users->get(1);
        $data = $this->getRequest()->getData();
        $Users->patchEntity($admin, $data);
        if (!$Users->save($admin)) {
            $this->log('Installer failed saving admin-data.');
            $this->set('admin', $admin);

            return;
        }
        $this->log('Installer admin-data is saved.');

        /// setting forum-default email
        $this->log('Installer setting forum email.');
        $Settings = TableRegistry::getTableLocator()->get('Settings');
        $forumEmail = $Settings->findByName('forum_email')->first();
        $forumEmail->set('value', $data['user_email']);
        $Settings->save($forumEmail);
        $this->log('Installer forum email set.');

        $this->log('Marking installed.');
        (new DbVersion($this->loadModel('Settings')))->set(Configure::read('Saito.v'));

        return $this->installerRedirect('finished');
    }

    /**
     * Installer finished
     *
     * @return \Cake\Http\Response|void
     */
    public function finished()
    {
        if (!InstallerState::check('finished')) {
            return $this->redirect('/');
        }

        $this->log('Installer finished.');
    }

    /**
     * {@inheritdoc}
     */
    public function log($msg, $level = LogLevel::INFO, $context = ['saito.install'])
    {
        parent::log($msg, $level, $context);
    }

    /**
     * Redirect to a different installer stage
     *
     * @param string $action controller-action to redirect to
     * @return Response redirect
     */
    private function installerRedirect(string $action): Response
    {
        InstallerState::set($action);

        return $this->redirect('/install/' . $action);
    }
}
