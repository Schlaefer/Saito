<?php
/**
 * BookmarkFixture
 *
 */
class BookmarkFixture extends CakeTestFixture {

/**
 * Fields
 *
 * @var array
 */
	public $fields = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
		'user_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'index'),
		'entry_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'index'),
		'comment' => array('type' => 'string', 'null' => false, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'created' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'modified' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1),
			'bookmarks_entryId_userId' => array('column' => array('entry_id', 'user_id'), 'unique' => 0),
			'bookmarks_userId' => array('column' => 'user_id', 'unique' => 0)
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
			'user_id' => 3,
			'entry_id' => 1,
			'comment' => '',
			'created' => '2012-08-07 09:51:45',
			'modified' => '2012-08-07 09:51:45'
		),
		array(
			'id' => 2,
			'user_id' => 3,
			'entry_id' => 3,
			'comment' => '< Comment 2',
			'created' => '2012-08-07 19:51:45',
			'modified' => '2012-08-07 19:51:45'
		),
		array(
			'id' => 3,
			'user_id' => 1,
			'entry_id' => 1,
			'comment' => 'Comment 3',
			'created' => '2012-08-07 09:51:45',
			'modified' => '2012-08-07 09:51:45'
		),
		array(
			'id' => 4,
			'user_id' => 2,
			'entry_id' => 4,
			'comment' => 'Comment 4',
			'created' => '2012-08-07 09:51:45',
			'modified' => '2012-08-07 09:51:45'
		),
		[
				'id' => 5,
				'user_id' => 3,
				'entry_id' => 11,
				'comment' => '<BookmarkComment',
				'created' => '2012-08-07 09:51:45',
				'modified' => '2012-08-07 09:51:45'
		]
	);

}
