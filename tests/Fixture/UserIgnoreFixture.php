<?php
/**
 * UserIgnoreFixture
 *
 */
class UserIgnoreFixture extends CakeTestFixture {

/**
 * Fields
 *
 * @var array
 */
	public $fields = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => true, 'key' => 'primary'),
		'created' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'modified' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'user_id' => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => false),
		'blocked_user_id' => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => false),
		'timestamp' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1)
		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB')
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
	'created' => '2014-08-07 17:48:57',
	'modified' => '2014-08-07 17:48:57',
	'user_id' => 1,
	'blocked_user_id' => 1,
	'timestamp' => '2014-08-07 17:48:57'
		),
	);
	*/

}
