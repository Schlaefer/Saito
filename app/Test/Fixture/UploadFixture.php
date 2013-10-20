<?php

class UploadFixture extends CakeTestFixture {

	public $name = 'Upload';

	public $fields = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'key' => 'primary'),
		'name' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 100),
		'type' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 100),
		'size' => array('type' => 'integer', 'null' => true, 'default' => null),
		'created' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'modified' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'user_id' => array('type' => 'integer', 'null' => true, 'default' => null),
		'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1)),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_unicode_ci', 'engine' => 'MyISAM')
	);

	public $records = array(
		array(
			'id' => 1,
			'name' => '3_upload_test.png',
			'type' => 'png',
			'size' => '10000',
			'user_id' => '3',
		),
		array(
			'id' => 2,
			'name' => '1_upload_test.png',
			'type' => 'jpg',
			'size' => '20000',
			'user_id' => '1',
		)
	);

}
