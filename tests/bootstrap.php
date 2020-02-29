<?php
declare(strict_types=1);

/**
 * Test runner bootstrap.
 *
 * Add additional configuration/setup your application needs when running
 * unit tests in this file.
 */

use Cake\Cache\Cache;
use Cake\Cache\Engine\ArrayEngine;
use Cake\Core\Configure;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;

require dirname(__DIR__) . '/vendor/autoload.php';

require dirname(__DIR__) . '/config/bootstrap.php';

$_SERVER['PHP_SELF'] = '/';

/// Use in-memory cache engine for tests
foreach (Cache::configured() as $cacheKey) {
    $config = Cache::getConfigOrFail($cacheKey);
    $config['className'] = ArrayEngine::class;
    Cache::drop($cacheKey);
    Cache::setConfig($cacheKey, $config);
}

// otherwise Security mock fails with debug info
Configure::write('debug', true);

define('TEST_TMP_DIR', TMP . 'tests' . DS);

// test userupload in tmp directory
Configure::write('Saito.Settings.uploadDirectory', TEST_TMP_DIR);
Configure::read('Saito.Settings.uploader')
    ->setStorageFileSystem(new Filesystem(new Local(TEST_TMP_DIR)));

// disable <asset-url>?<timestamp> for tests
Configure::write('Asset.timestamp', false);

// Fixate sessionid early on, as php7.2+
// does not allow the sessionid to be set after stdout
// has been written to.
session_id('cli');
