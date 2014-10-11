<?php

	/**
	 * EcachFixture
	 *
	 */
	class EcachFixture extends CakeTestFixture {

		/**
		 * Fields
		 *
		 * @var array
		 */
		public $fields = array(
			'key' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 128, 'key' => 'primary', 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
			'value' => array('type' => 'binary', 'null' => false, 'default' => null),
			'indexes' => array(
				'PRIMARY' => array('column' => 'key', 'unique' => 1)
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
				'key' => 'Lorem ipsum dolor sit amet',
				'value' => 'Lorem ipsum dolor sit amet'
			),
		);

	}
