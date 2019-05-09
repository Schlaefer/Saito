<?php

	/**
	 * UserBlockFixture
	 *
	 */
	class UserBlockFixture extends CakeTestFixture {

		/**
		 * Fields
		 *
		 * @var array
		 */
		public $fields = array(
			'id' => array('type' => 'integer', 'null' => false, 'default' => null,
				'unsigned' => true, 'key' => 'primary'),
			'created' => array('type' => 'datetime', 'null' => true,
				'default' => null),
			'modified' => array('type' => 'datetime', 'null' => true,
				'default' => null),
			'user_id' => array('type' => 'integer', 'null' => false,
				'default' => null, 'unsigned' => true, 'key' => 'index'),
			'reason' => array('type' => 'string', 'null' => true, 'default' => null,
				'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
			'blocked_by_user_id' => array('type' => 'integer', 'null' => true, 'default' => null,
				'unsigned' => true),
			'ends' => array('type' => 'datetime', 'null' => true, 'default' => null,
				'key' => 'index'),
			'ended' => array('type' => 'datetime', 'null' => true, 'default' => null),
			'hash' => array('type' => 'string', 'null' => true, 'default' => null,
				'length' => 32, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
			'indexes' => array(
				'PRIMARY' => array('column' => 'id', 'unique' => 1),
				'ends' => array('column' => 'ends', 'unique' => 0),
				'user_id' => array('column' => 'user_id', 'unique' => 0)
			),
			'tableParameters' => array('charset' => 'utf8',
				'collate' => 'utf8_general_ci', 'engine' => 'InnoDB')
		);

		/**
		 * Records
		 *
		 * @var array
		 */
		public $records = array(
			array(
				'id' => 1,
				'created' => '2014-08-11 08:59:43',
				'modified' => '2014-08-11 08:59:43',
				'user_id' => 1,
				'reason' => 1,
				'ends' => '2014-08-11 08:59:43',
				'blocked_by_user_id' => 1,
				'ended' => null
			),
			array(
				'id' => 2,
				'created' => '2014-08-11 08:59:43',
				'modified' => '2014-08-11 08:59:43',
				'user_id' => 2,
				'reason' => 1,
				'ends' => null,
				'blocked_by_user_id' => 1,
				'ended' => null
			),
		);

	}
