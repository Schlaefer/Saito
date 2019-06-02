<?php

declare(strict_types = 1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers 2018
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

use Api\Error\JsonApiExceptionRenderer;
use Cake\Core\Configure;

// @bogus
$getUri = function () {
    if (!empty($_SERVER['PATH_INFO'])) {
        $uri = $_SERVER['PATH_INFO'];
    } elseif (isset($_SERVER['REQUEST_URI'])) {
        $uri = $_SERVER['REQUEST_URI'];
    } elseif (isset($_SERVER['PHP_SELF']) && isset($_SERVER['SCRIPT_NAME'])) {
        $uri = str_replace($_SERVER['SCRIPT_NAME'], '', $_SERVER['PHP_SELF']);
    } elseif (isset($_SERVER['HTTP_X_REWRITE_URL'])) {
        $uri = $_SERVER['HTTP_X_REWRITE_URL'];
    } elseif ($var = env('argv')) {
        $uri = $var[0];
    } else {
        throw new \Exception('Could not evaluate URL', 155949137);
    }

    return $uri;
};

if (strstr($getUri(), 'api/')) {
    Configure::write('Error.exceptionRenderer', JsonApiExceptionRenderer::class);
}
