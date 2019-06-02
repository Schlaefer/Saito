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

use App\Model\Table\SettingsTable;
use Cake\Cache\Cache;
use Cake\Controller\Controller;
use Cake\Core\Configure;
use Cake\Core\Plugin;
use Cake\Datasource\ConnectionManager;
use Cake\I18n\I18n;
use Cake\Log\LogTrait;
use Migrations\Migrations;

/**
 * Installer App Controller
 *
 * @property SettingsTable $Settings
 */
class AppController extends Controller
{
    use LogTrait;

    /** @var Migrations */
    protected $migrations;

    public $helpers = [
        'Breadcrumbs' => ['className' => 'BootstrapUI.Breadcrumbs'],
        'Flash' => ['className' => 'BootstrapUI.Flash'],
        'Form' => ['className' => 'BootstrapUI.Form'],
        'Html' => ['className' => 'BootstrapUI.Html'],
        'Paginator' => ['className' => 'BootstrapUI.Paginator'],
    ];

    /**
     * {@inheritDoc}
     */
    public function initialize()
    {
        Cache::clear();
        Cache::disable();

        parent::initialize();

        $this->loadModel('Settings');

        $configName = $this->Settings->getConnection()->configName();
        $this->migrations = new Migrations(['connection' => $configName]);

        $locale = Configure::read('Saito.language');
        I18n::setLocale($locale);
    }
}
