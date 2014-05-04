<?php

	/**
	 * UseronlineFixture
	 *
	 */
	class UseronlineFixture extends CakeTestFixture {

		/**
		 * Table name
		 *
		 * @var string
		 */
		public $table = 'useronline';

		/**
		 * Fields
		 *
		 * @var array
		 */
		public $fields = array(
			'id' => array('type' => 'integer', 'null' => false, 'default' => null,
				'unsigned' => true, 'key' => 'primary'),
			'uuid' => array('type' => 'string', 'null' => false, 'length' => 32,
				'key' => 'unique', 'collate' => 'utf8_unicode_ci', 'charset' => 'utf8'),
			'user_id' => array('type' => 'integer', 'null' => true, 'default' => null,
				'unsigned' => false, 'key' => 'index'),
			'logged_in' => array('type' => 'boolean', 'null' => false,
				'default' => null, 'key' => 'index'),
			'time' => array('type' => 'integer', 'null' => false, 'default' => '0',
				'length' => 14, 'unsigned' => false),
			'created' => array('type' => 'datetime', 'null' => true,
				'default' => null),
			'modified' => array('type' => 'datetime', 'null' => true,
				'default' => null),
			'indexes' => array(
				'PRIMARY' => array('column' => 'id', 'unique' => 1),
				'uuid' => array('column' => 'uuid', 'unique' => 1),
				'user_id' => array('column' => 'user_id', 'unique' => 0),
				'logged_in' => array('column' => 'logged_in', 'unique' => 0)
			),
			'tableParameters' => array('charset' => 'utf8',
				'collate' => 'utf8_unicode_ci', 'engine' => 'MEMORY')
		);

		/**
		 * Records
		 *
		 * @var array
		 */
		/*
		public $records = array(
			array(
				'id' => 1,
				'uuid' => 'Lorem ipsum dolor sit amet',
				'user_id' => 1,
				'logged_in' => 1,
				'time' => 1,
				'created' => '2014-05-04 07:47:53',
				'modified' => '2014-05-04 07:47:53'
			),
		);
		*/
	}
