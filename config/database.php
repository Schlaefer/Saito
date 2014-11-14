<?php

class DATABASE_CONFIG {

	var $default = array(
		'datasource' => 'Database/Mysql',
		'persistent' => false,
		'host' => '127.0.0.1',
		'login' => 'user',
		'password' => 'user',
		'database' => 'default',
		'prefix' => '',
		'encoding' => 'utf8',
//		'port' => 4444
		// 'port' => '/private/tmp/mysql.sock',
	);

	/**
	 * @var array Postgresql
	 */
	/*
	var $default = array(
		'datasource' => 'Database/Postgres',
		'persistent' => false,
		'host' => '127.0.0.1',
		'login' => 'siezi',
		'password' => '',
		'database' => 'default',
		'prefix' => '',
		'encoding' => 'utf8',
		'port' => '5432',
	);
	*/

	var $test = array(
		'datasource' => 'Database/Mysql',
		'persistent' => false,
		'host' => '127.0.0.1',
		'login' => 'user',
		'password' => 'user',
		'database' => 'test',
		'prefix' => '',
		'encoding' => 'utf8',
//		'port' => 4444
		// 'port' => '/private/tmp/mysql.sock',
	);

	/*** Don't edit below this line! ***/

	public $saitoHelp = [
		'datasource' => 'SaitoHelp.SaitoHelpSource'
	];

	public function __construct() {
		if(php_sapi_name() !== 'cli') {
			$this->default['port'] = 4444;
		}

	  return;
		if ($this->_isSelenium()) {
			$this->default = $this->test;
		}
	}

	/**
	 * Selenium is not detectable by itself, but we try with this @bogus checks
	 *
	 * @return bool if request is made by selenium server
	 */
	protected function _isSelenium() {
		return 	isset($_SERVER['SERVER_NAME'])
						&& strpos($_SERVER['SERVER_NAME'], 'localhost') !== false
						&& Configure::read('debug')
						&& isset($_SERVER['HTTP_X_FORWARDED_FOR'])
						&& $_SERVER['HTTP_X_FORWARDED_FOR'] === "0:0:0:0:0:0:0:1";
	}
}
