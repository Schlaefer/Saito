<?php
/**
 * ShoutFixture
 *
 */
class ShoutFixture extends CakeTestFixture {

/**
 * Fields
 *
 * @var array
 */
	public $fields = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'key' => 'primary'),
		'created' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'modified' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'text' => array('type' => 'string', 'null' => true, 'default' => null, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'user_id' => array('type' => 'integer', 'null' => true, 'default' => null),
		'time' => array( 'type' => 'timestamp', 'null' => false, 'default' => null ),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1)
		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'MEMORY')
	);

/**
 * Records
 *
 * @var array
 */
	public $records = array(
		array(
			'id' => 2,
			'time' => '2013-02-08 11:49:31',
			'text' => 'Lorem ipsum dolor sit amet',
			'user_id' => 1
		),
		array(
			'id' => 3,
			'time' => '2013-02-08 11:49:31',
			'text' => 'Lorem ipsum dolor sit amet',
			'user_id' => 1
		),
		array(
			'id' => 4,
			'time' => '2013-02-08 11:49:31',
			'text' => "<script></script>[i]italic[/i]",
			'user_id' => 1
		),
	);

}
