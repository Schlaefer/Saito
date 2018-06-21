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
use Cake\I18n\I18n;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Installer\Lib\DbVersion;
use Psr\Log\LogLevel;

/**
 * Updater Controller
 */
class UpdaterController extends AppController
{
    /** @var string */
    private $dbVersion;

    /** @var string */
    private $saitoVersion;

    /**
     * {@inheritDoc}
     */
    public function initialize()
    {
        parent::initialize();
        $this->dbVersion = Configure::read('Saito.Settings.db_version');
        $this->saitoVersion = Configure::read('Saito.v');
    }

    /**
     * Index method
     *
     * @return \Cake\Http\Response|void
     */
    public function start()
    {
        $token = new File(CONFIG . 'updater');

        if ($token->exists()) {
            $this->renderFailure(__d('installer', 'update.failure.explanation'), 1529737182);

            return;
        }

        $this->set('dbVersion', $this->dbVersion);
        $this->set('saitoVersion', $this->saitoVersion);

        if (empty($this->dbVersion)) {
            $msg = __d('installer', 'update.failure.nodbversion');
            $this->renderFailure($msg, 1529737397);
            $this->log($msg, LogLevel::ERROR, ['saito.install']);

            return;
        }

        if (version_compare($this->dbVersion, '4.10.0', '<')) {
            $msg = __d('installer', 'update.failure.wrongdbversion', ['v' => $this->dbVersion]);
            $this->renderFailure($msg, 1529737648);
            $this->log($msg, LogLevel::ERROR, ['saito.install']);

            return;
        }

        if (!$this->getRequest()->is('post')) {
            return;
        }

        $this->logCurrentState('Pre-update state.');

        $token->write('');

        try {
            if (version_compare($this->dbVersion, '4.10.0', '==')) {
                $this->migrations->markMigrated('20180620081553');
                $this->logCurrentState('Marked version 4.10.0 migrated.');
            }

            $this->migrations->migrate();
            (new DbVersion($this->Settings))->set($this->saitoVersion);
            $this->logCurrentState('Post upgrade state.');
        } catch (\Throwable $e) {
            $this->logCurrentState('Migration failed: ' . $e->getMessage());

            return $this->redirect('/');
        }

        $token->delete();
        $this->viewBuilder()->setTemplate('success');

        $this->logCurrentState('Update successfull.');
    }

    /**
     * Render the update error template
     *
     * @param string $message message for failure
     * @param int $code failure-code
     * @return void
     */
    private function renderFailure(string $message, int $code)
    {
        $this->viewBuilder()->setTemplate('failure');
        $this->set('code', $code);
        $this->set('incident', $message);
    }

    /**
     * Log current strate
     *
     * @param string $message message for log
     * @return void
     */
    private function logCurrentState(string $message)
    {
        $this->log($message, LogLevel::INFO, ['saito.install']);
        $content = [
            'date' => date('c'),
            'dbVersion' => $this->dbVersion,
            'saitoVersion' => $this->saitoVersion,
            'status' => $this->migrations->status(),
        ];
        $this->log(print_r($content, true), LogLevel::INFO, ['saito.install']);
    }
}
