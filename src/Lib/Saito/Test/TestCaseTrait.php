<?php

namespace Saito\Test;

use Aura\Di\Config;
use Cake\Cache\Cache;
use Cake\Core\Configure;
use Cake\Network\Email\Email;
use Cake\Utility\Inflector;
use Cron\Lib\Cron;
use Saito\App\Registry;
use Saito\User\ForumsUserInterface;
use Saito\User\SaitoUser;

trait TestCaseTrait
{

    /**
     * @var \Aura\Di\Container
     */
    protected $dic;

    protected $saitoSettings;

    protected function setUpSaito()
    {
        $this->initDic();
        $this->storeSettings();
        $this->mockMailTransporter();
        $this->clearCaches();
    }

    protected function tearDownSaito()
    {
        $this->restoreSettings();
        $this->clearCaches();
    }

    protected function clearCaches()
    {
        Cache::clear();
        Cache::clear(false, 'short');
    }

    /**
     * Setup for dependency injection container
     *
     * @return \Aura\Di\Container
     */
    public function initDic(ForumsUserInterface $User = null)
    {
        $this->dic = Registry::initialize();
        if ($User === null) {
            $User = new SaitoUser();
        }
        $this->dic->set('CU', $User);

        $this->dic->set('Cron', new Cron());
    }

    protected function storeSettings()
    {
        $this->saitoSettings = Configure::read('Saito.Settings');
    }

    protected function restoreSettings()
    {
        if ($this->saitoSettings !== null) {
            Configure::write('Saito.Settings', $this->saitoSettings);
        }
    }

    protected function getMockForTable($table, array $methods = [])
    {
        $tableName = Inflector::underscore($table);
        $Mock = $this->getMockForModel(
            $table,
            $methods,
            ['table' => strtolower($tableName)]
        );

        return $Mock;
    }

    protected function mockMailTransporter()
    {
        $mock = $this->getMock('Cake\Network\Email\DebugTransport');
        Email::dropTransport('saito');
        Email::configTransport('saito', $mock);

        return $mock;
    }

}
