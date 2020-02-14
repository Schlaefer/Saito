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

use Cake\Cache\Cache;
use Cake\Controller\Controller;
use Cake\Core\Configure;
use Cake\Datasource\ConnectionManager;
use Cake\I18n\I18n;
use Cake\Log\LogTrait;
use Cake\ORM\Table;
use Migrations\Migrations;

/**
 * Installer App Controller
 *
 * @property \App\Model\Table\SettingsTable $Settings
 */
class AppController extends Controller
{
    use LogTrait;

    /**
     * @var \Migrations\Migrations
     */
    protected $migrations;

    /**
     * {@inheritDoc}
     */
    public function initialize(): void
    {
        Cache::clear();
        Cache::disable();

        parent::initialize();

        $this->loadModel('Settings');
        $this->migrations = $this->initializeMigrations($this->Settings);

        $this->viewBuilder()->setHelpers(
            [
                'Breadcrumbs' => ['className' => 'BootstrapUI.Breadcrumbs'],
                'Flash' => ['className' => 'BootstrapUI.Flash'],
                'Form' => ['className' => 'BootstrapUI.Form'],
                'Html' => ['className' => 'BootstrapUI.Html'],
                'Paginator' => ['className' => 'BootstrapUI.Paginator'],
            ] + $this->viewBuilder()->getHelpers()
        );

        $locale = Configure::read('Saito.language');
        I18n::setLocale($locale);
    }

    /**
     * Initialize migration property
     *
     * @param \Cake\ORM\Table $table a table to read the config from
     * @return \Migrations\Migrations
     */
    private function initializeMigrations(Table $table)
    {
        $installerConfigName = 'installer';
        // if: static configuration only allowed once, but done multiple times in test-cases
        if (ConnectionManager::getConfig($installerConfigName) === null) {
            $defaultConfigName = $table->getConnection()->configName();
            $connectionConfig = ConnectionManager::getConfig($defaultConfigName);
            $connectionConfig['quoteIdentifiers'] = true;
            ConnectionManager::setConfig($installerConfigName, $connectionConfig);
        }

        return new Migrations(['connection' => $installerConfigName]);
    }
}
