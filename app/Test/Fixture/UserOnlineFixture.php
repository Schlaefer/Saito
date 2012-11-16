<?php

	class UserOnlineFixture extends CakeTestFixture {

		public $table = 'useronline';

		public $fields = array(
			'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'key' => 'primary'),
			'created' => array('type' => 'datetime', 'null' => true, 'default' => null),
			'modified' => array('type' => 'datetime', 'null' => true, 'default' => null),
			'time' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 14),
			'user_id' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 32, 'key' => 'index', 'collate' => 'utf8_unicode_ci', 'charset' => 'utf8'),
			'logged_in' => array('type' => 'boolean', 'null' => false, 'default' => null, 'key' => 'index'),
			'indexes' => array(
				'PRIMARY' => array('column' => 'id', 'unique' => 1),
				'user_id' => array('column' => 'user_id', 'unique' => 0),
				'logged_in' => array('column' => 'logged_in', 'unique' => 0)
			),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_unicode_ci', 'engine' => 'MEMORY')
		);

		/*
		  var $records = array (
		  // 2010-06-17 09:05:28 GMT
		  array ( 'id' => '1', 'user_id' => '1', 'time' => 1276765527, 'ip' => 'uid_1' ),
		  array ( 'id' => '2', 'user_id' => '2', 'time' => 1276765528, 'ip' => 'uid_2' ),
		  array ( 'id' => '3', 'user_id' => '3', 'time' => 1276765528, 'ip' => 'uid_3' ),
		  array ( 'id' => '10', 'user_id' => '0', 'time' => 1276765528, 'ip' => '192.168.0.10' ),
		  );
		 */
	}