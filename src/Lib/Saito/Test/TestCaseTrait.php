<?php
declare(strict_types=1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace Saito\Test;

use Cake\Core\Configure;
use Cake\Event\EventManager;
use Cake\Filesystem\File;
use Cake\I18n\I18n;
use Cake\Mailer\Transport\DebugTransport;
use Cake\Mailer\TransportFactory;
use Cake\ORM\TableRegistry;
use Cake\Utility\Inflector;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use Saito\App\Registry;
use Saito\Cache\CacheSupport;

trait TestCaseTrait
{
    private $saitoSettings;

    protected $saitoPermissions;

    /**
     * set-up saito
     *
     * @return void
     */
    protected function setUpSaito()
    {
        Registry::initialize();

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
        $this->_clearTmpFolder();
    }

    /**
     * Clear test tmp folder
     * @return void
     */
    protected function _clearTmpFolder(): void
    {
        $this->_clearFolder();
    }

    /**
     * Clears a folder within the test folder
     * @param null|string $path  Path to folder (null clears root)
     * @return void
     */
    private function _clearFolder(?string $path = null): void
    {
        $fs = new Filesystem(new Local(TEST_TMP_DIR));
        $contents = $fs->listContents($path);
        foreach ($contents as $content) {
            $basename = $content['basename'];
            if (strpos($basename, '.') === 0) {
                continue;
            }
            $fs->delete($basename);
        }
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
     * store global settings
     *
     * @return void
     */
    protected function _storeSettings()
    {
        $this->saitoSettings = Configure::read('Saito.Settings');
        $this->saitoPermissions = clone Configure::read('Saito.Permission.Resources');
        $this->setI18n('en');
        Configure::write('Saito.Settings.ParserPlugin', \Plugin\BbcodeParser\src\Lib\Markup::class);
        Configure::write('Saito.Settings.uploader', clone $this->saitoSettings['uploader']);
    }

    /**
     * restore global settings
     *
     * @return void
     */
    protected function _restoreSettings()
    {
        Configure::write('Saito.Settings', $this->saitoSettings);
        Configure::write('Saito.Permission.Resources', $this->saitoPermissions);
    }

    /**
     * Set the current translation language
     *
     * @param string $lang language code
     * @return void
     */
    public function setI18n(string $lang): void
    {
        Configure::write('Saito.language', $lang);
        I18n::setLocale($lang);
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
     * Insert categories into permissions
     *
     * @return void
     */
    protected function insertCategoryPermissions(): void
    {
        Registry::get('Permissions')
            ->buildCategories(TableRegistry::getTableLocator()->get('Categories'));
    }

    /**
     * Mock mailtransporter
     *
     * @return mixed
     */
    protected function mockMailTransporter()
    {
        $mock = $this->createMock(DebugTransport::class);
        TransportFactory::drop('saito');
        TransportFactory::setConfig('saito', $mock);

        return $mock;
    }

    /**
     * Creates a mock image file in $file
     *
     * @param \Cake\Filesystem\File $file File with extension.
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
