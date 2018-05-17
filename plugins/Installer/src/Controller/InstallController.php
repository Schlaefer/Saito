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

use Cake\Controller\Controller;
use Cake\Core\Configure;
use Cake\Core\Plugin;
use Cake\Datasource\ConnectionManager;
use Cake\Datasource\ConnectionRegistry;
use Cake\Event\Event;
use Cake\I18n\I18n;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Migrations\Migrations;

/**
 * Install Controller
 */
class InstallController extends Controller
{
    public $helpers = [
        'Breadcrumbs' => ['className' => 'BootstrapUI.Breadcrumbs'],
        'Flash' => ['className' => 'BootstrapUI.Flash'],
        'Form' => ['className' => 'BootstrapUI.Form'],
        'Html' => ['className' => 'BootstrapUI.Html'],
        'Paginator' => ['className' => 'BootstrapUI.Paginator'],
    ];

    /**
     * Index method
     *
     * @return \Cake\Http\Response|void
     */
    public function start()
    {
        $locale = Configure::read('Saito.language');
        I18n::setLocale($locale);

        $database = $tables = false;
        try {
            $connection = ConnectionManager::get('default');
            $connected = $connection->connect();
            $database = true;
        } catch (\Throwable $connectionError) {
        }

        $this->set(compact('database', 'tables'));

        if (!$database) {
            return;
        }

        $data = $this->request->getData();
        Plugin::load('Migrations');
        $migration = new Migrations();

        $status = $migration->status();
        if (empty($status[0]) || empty($status[0]['status']) || $status[0]['status'] !== 'down') {
            $tables = true;
            $this->set(compact('database', 'tables'));

            return;
        }

        if (empty($data)) {
            return;
        }

        $migration->migrate();
        $migration->seed();

        $Users = TableRegistry::getTableLocator()->get('Users', ['className' => Table::class]);
        $admin = $Users->get(1);
        // @todo why not done in userstable?
        $data['password'] = md5($data['password']);
        $Users->patchEntity($admin, $data);
        if (!$Users->save($admin)) {
            return;
        }

        $tables = true;

        $this->set(compact('database', 'tables'));
    }
}
