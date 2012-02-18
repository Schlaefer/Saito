<?php
/* SmileyCode Fixture generated on: 2011-05-17 13:51:01 : 1305633061 */
class SmileyCodeFixture extends CakeTestFixture {
	var $name = 'SmileyCode';

	var $fields = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'key' => 'primary'),
		'smiley_id' => array('type' => 'integer', 'null' => false, 'default' => '0'),
		'code' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 32, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1)),
		'tableParameters' => array()
	);

	var $records = array(
		array(
			'id' => 1,
			'smiley_id' => 1,
			'code' => ':-)'
		),
		array(
			'id' => 2,
			'smiley_id' => 1,
			'code' => ';-)'
		),
		array(
			'id' => 3,
			'smiley_id' => 2,
			'code' => ';)'
		),
	);
}
?>