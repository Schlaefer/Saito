<?php
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
use Cake\Core\Configure;

Configure::write('debug', true);

// Cake Session isn't isolated and clashes with PHPUnit
// @see https://github.com/sebastianbergmann/phpunit/issues/1416
session_id('cli');

// test userupload in tmp directory
Configure::write('Saito.Settings.uploadDirectory', TMP . 'tests' . DS);
