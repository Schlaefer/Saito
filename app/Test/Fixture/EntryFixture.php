<?php

	class EntryFixture extends CakeTestFixture {

		// public $import = array( 'table' => 'entries' );
		public $fields = array(
			'created' => array('type' => 'datetime', 'null' => false, 'default' => NULL, 'collate' => NULL, 'comment' => ''),
			'modified' => array('type' => 'datetime', 'null' => false, 'default' => NULL, 'collate' => NULL, 'comment' => ''),
			'id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'key' => 'primary', 'collate' => NULL, 'comment' => ''),
			'pid' => array('type' => 'integer', 'null' => false, 'default' => '0', 'key' => 'index', 'collate' => NULL, 'comment' => ''),
			'tid' => array('type' => 'integer', 'null' => false, 'default' => '0', 'key' => 'index', 'collate' => NULL, 'comment' => ''),
			'uniqid' => array('type' => 'string', 'null' => true, 'default' => NULL, 'collate' => 'utf8_general_ci', 'comment' => '', 'charset' => 'utf8'),
			'time' => array('type' => 'timestamp', 'null' => false, 'default' => 'CURRENT_TIMESTAMP', 'key' => 'index', 'collate' => NULL, 'comment' => ''),
			'last_answer' => array('type' => 'timestamp', 'null' => false, 'default' => '0000-00-00 00:00:00', 'key' => 'index', 'collate' => NULL, 'comment' => ''),
			'edited' => array('type' => 'timestamp', 'null' => false, 'default' => '0000-00-00 00:00:00', 'collate' => NULL, 'comment' => ''),
			'edited_by' => array('type' => 'string', 'null' => true, 'default' => NULL, 'collate' => 'utf8_general_ci', 'comment' => '', 'charset' => 'utf8'),
			'user_id' => array('type' => 'integer', 'null' => true, 'default' => '0', 'key' => 'index', 'collate' => NULL, 'comment' => ''),
			'name' => array('type' => 'string', 'null' => true, 'default' => NULL, 'collate' => 'utf8_general_ci', 'comment' => '', 'charset' => 'utf8'),
			'subject' => array('type' => 'string', 'null' => true, 'default' => NULL, 'key' => 'index', 'collate' => 'utf8_general_ci', 'comment' => '', 'charset' => 'utf8'),
			'category' => array('type' => 'integer', 'null' => false, 'default' => '0', 'collate' => NULL, 'comment' => ''),
			'text' => array('type' => 'text', 'null' => true, 'default' => NULL, 'collate' => 'utf8_general_ci', 'comment' => '', 'charset' => 'utf8'),
			'email_notify' => array('type' => 'integer', 'null' => true, 'default' => '0', 'length' => 4, 'collate' => NULL, 'comment' => ''),
			'locked' => array('type' => 'integer', 'null' => true, 'default' => '0', 'length' => 4, 'collate' => NULL, 'comment' => ''),
			'fixed' => array('type' => 'integer', 'null' => true, 'default' => '0', 'length' => 4, 'collate' => NULL, 'comment' => ''),
			'views' => array('type' => 'integer', 'null' => true, 'default' => '0', 'collate' => NULL, 'comment' => ''),
			'flattr' => array('type' => 'boolean', 'null' => true, 'default' => NULL, 'collate' => NULL, 'comment' => ''),
			'nsfw' => array('type' => 'boolean', 'null' => true, 'default' => NULL, 'collate' => NULL, 'comment' => ''),
		);

		public $records = array(
				//* thread 1
				array(
						'id' => 1,
						'subject' => 'First_Subject',
						'text' => 'First_Text',
						'pid' => 0,
						'tid' => 1,
						'time' => '2000-01-01 20:00:00',
						// accession = 0
						'category' => 2,
						'user_id' => 3,
				),
				array(
						'id' => 2,
						'subject' => 'Second_Subject',
						'text' => 'Second_Text',
						'pid' => 1,
						'tid' => 1,
						'time' => '2000-01-01 20:01:00',
						'category' => 2,
						'user_id' => 2,
				),
				array(
						'id' => 3,
						'subject' => 'Third_Subject',
						'text' => 'Third_Text',
						'pid' => 2,
						'tid' => 1,
						'time' => '2000-01-01 20:02:00',
						'category' => 2,
						'user_id' => 3,
				),
				//* thread 2
				array(
						'id' => 4,
						'subject' => 'Second Thread First_Subject',
						'text' => '',
						'pid' => 0,
						'tid' => 4,
						'time' => '2000-01-01 10:00:00',
						// accession = 1
						'category' => 4,
						'user_id' => 1,
				)
		);

	}

?>