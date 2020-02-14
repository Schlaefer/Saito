<?php
declare(strict_types=1);

/**
 * Test runner bootstrap.
 *
 * Add additional configuration/setup your application needs when running
 * unit tests in this file.
 */

use Cake\Core\Configure;

/**
 * Test runner bootstrap.
 *
 * Add additional configuration/setup your application needs when running
 * unit tests in this file.
 */
require dirname(__DIR__) . '/vendor/autoload.php';

require dirname(__DIR__) . '/config/bootstrap.php';

$_SERVER['PHP_SELF'] = '/';

// otherwise Security mock fails with debug info
Configure::write('debug', true);

// test userupload in tmp directory
Configure::write('Saito.Settings.uploadDirectory', TMP . 'tests' . DS);

// disable <asset-url>?<timestamp> for tests
Configure::write('Asset.timestamp', false);

// Fixate sessionid early on, as php7.2+
// does not allow the sessionid to be set after stdout
// has been written to.
session_id('cli');
