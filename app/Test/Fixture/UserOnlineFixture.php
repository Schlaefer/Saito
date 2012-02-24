<?php

	class UserOnlineFixture extends CakeTestFixture {

		public $table = 'useronline';
		public $fields = array(
				'id' => array( 'type' => 'integer', 'length' => 11, 'key' => 'primary' ),
				'user_id' => array( 'type' => 'string', 'length' => 32, 'default' => NULL ),
				'time' => array( 'type' => 'integer', 'length' => 14, 'default' => 0 ),
				'logged_in' => array( 'type' => 'integer', 'length' => 1 ),
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

?>