<?php

namespace Saito\Test;

use Cake\Core\Configure;
use Cake\Event\EventManager;
use Cake\Filesystem\File;
use Cake\Mailer\Email;
use Cake\Utility\Inflector;
use claviska\SimpleImage;
use Cron\Lib\Cron;
use Plugin\BbcodeParser\src\Lib\Markup;
use Saito\App\Registry;
use Saito\Cache\CacheSupport;
use Saito\User\ForumsUserInterface;
use Saito\User\SaitoUser;

trait TestCaseTrait
{

    /**
     * @var \Aura\Di\Container
     */
    protected $dic;

    protected $saitoSettings;

    /**
     * set-up saito
     *
     * @return void
     */
    protected function setUpSaito()
    {
        $this->initDic();
        $this->_storeSettings();
        $this->mockMailTransporter();
        $this->_clearCaches();
    }

    /**
     * tear down saito
     *
     * @return void
     */
    protected function tearDownSaito()
    {
        $this->_restoreSettings();
        $this->_clearCaches();
    }

    /**
     * clear caches
     *
     * @return void
     */
    protected function _clearCaches()
    {
        $CacheSupport = new CacheSupport();
        $CacheSupport->clear();
        EventManager::instance()->off($CacheSupport);
        unset($CacheSupport);
    }

    /**
     * Setup for dependency injection container
     *
     * @param ForumsUserInterface $User user
     * @return void
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

    /**
     * store global settings
     *
     * @return void
     */
    protected function _storeSettings()
    {
        $this->saitoSettings = Configure::read('Saito.Settings');
        Configure::write('Saito.language', 'en');
        Configure::write('Saito.Settings.ParserPlugin', \Plugin\BbcodeParser\src\Lib\Markup::class);
    }

    /**
     * restore global settings
     *
     * @return void
     */
    protected function _restoreSettings()
    {
        if ($this->saitoSettings !== null) {
            Configure::write('Saito.Settings', $this->saitoSettings);
        }
    }

    /**
     * Mock table
     *
     * @param string $table table
     * @param array $methods methods to mock
     * @return mixed
     */
    public function getMockForTable($table, array $methods = [])
    {
        $tableName = Inflector::underscore($table);
        $Mock = $this->getMockForModel(
            $table,
            $methods,
            ['table' => strtolower($tableName)]
        );

        return $Mock;
    }

    /**
     * Mock mailtransporter
     *
     * @return mixed
     */
    protected function mockMailTransporter()
    {
        $mock = $this->createMock('Cake\Mailer\Transport\DebugTransport');
        Email::dropTransport('saito');
        Email::setConfigTransport('saito', $mock);

        return $mock;
    }

    /**
     * Creates a mock image file in $file
     *
     * @param File $file File with extension.
     *
     * Mime type is taken from extension. Allowed extensions: png, jpeg, jpg
     *
     * @param int $size size of the mock image in kB
     * @return void
     */
    protected function mockMediaFile(File $file, int $size = 100): void
    {
        //// Create single pixel image
        $Image = imagecreatetruecolor(1, 1);
        imagesetpixel($Image, 0, 0, imagecolorallocate($Image, 0, 0, 0));

        switch ($file->ext()) {
            case 'jpeg':
            case 'jpg':
                imagejpeg($Image, $file->path);
                break;
            case 'png':
                imagepng($Image, $file->path);
                break;
            default:
                throw new \InvalidArgumentException();
        }

        // pad to saze with garbage data
        $file->append(str_repeat('0', $size * 1024));
    }
}
