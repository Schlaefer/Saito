<?php
/**
 * EseventFixture
 *
 */
class EseventFixture extends CakeTestFixture {

/**
 * Fields
 *
 * @var array
 */
	public $fields = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
		'subject' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'index'),
		'event' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1),
			'subject_event' => array('column' => array('subject', 'event'), 'unique' => 0)
		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_unicode_ci', 'engine' => 'MyISAM')
	);

/**
 * Records
 *
 * @var array
 */
	public $records = array(
		array(
			'id' => 1,
			'subject' => 1,
			'event' => 1
		),
		array(
			'id' => 2,
			'subject' => 1,
			'event' => 2
		),
		array(
			'id' => 3,
			'subject' => 2,
			'event' => 1
		),
		array(
			'id' => 4,
			'subject' => 1,
			'event' => 3
		),
	);

}
