<?php

/**
 * @author Frank Nägler
 * @link https://philecms.com
 * @license http://opensource.org/licenses/MIT
 * @package Phile
 */
define('PHILE_VERSION',    '1.1.1');
define('ROOT_DIR',         realpath(dirname(__FILE__)) . DIRECTORY_SEPARATOR);
define('CONTENT_DIR',      ROOT_DIR . 'content' . DIRECTORY_SEPARATOR);
define('CONTENT_EXT',      '.md');
define('LIB_DIR',          ROOT_DIR . 'lib' . DIRECTORY_SEPARATOR);
define('PLUGINS_DIR',      ROOT_DIR . 'plugins' . DIRECTORY_SEPARATOR);
define('THEMES_DIR',       ROOT_DIR . 'themes' . DIRECTORY_SEPARATOR);
define('CACHE_DIR',        LIB_DIR . 'cache' . DIRECTORY_SEPARATOR);


spl_autoload_extensions(".php");
spl_autoload_register(function ($className) {
	$fileName = LIB_DIR . str_replace("\\", DIRECTORY_SEPARATOR, $className) . '.php';
	if (file_exists($fileName)) {
		require_once $fileName;
	} else {
		// autoload plugin namespace
		if (strpos($className, "Phile\\Plugin\\") === 0) {
			$className 		= substr($className, 13);
			$classNameParts = explode('\\', $className);
			$pluginVendor 	= lcfirst(array_shift($classNameParts));
			$pluginName 	= lcfirst(array_shift($classNameParts));
			$classPath		= array_merge(array($pluginVendor, $pluginName, 'Classes'), $classNameParts);
			$fileName 		= PLUGINS_DIR . implode(DIRECTORY_SEPARATOR, $classPath) . '.php';
			if (file_exists($fileName)) {
				require_once $fileName;
			}
		}
	}
});

require(LIB_DIR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php');

ob_start();

try {
	$phileCore = new \Phile\Core();
	echo $phileCore->render();
} catch (\Phile\Exception $e) {
	if (\Phile\ServiceLocator::hasService('Phile_ErrorHandler')) {
		ob_end_clean();

		/** @var \Phile\ServiceLocator\ErrorHandlerInterface $errorHandler */
		$errorHandler = \Phile\ServiceLocator::getService('Phile_ErrorHandler');
		$errorHandler->handleException($e);
	}
} catch (\Exception $e) {
	if (\Phile\ServiceLocator::hasService('Phile_ErrorHandler')) {
		ob_end_clean();

		/** @var \Phile\ServiceLocator\ErrorHandlerInterface $errorHandler */
		$errorHandler = \Phile\ServiceLocator::getService('Phile_ErrorHandler');
		$errorHandler->handleException($e);
	}
}
