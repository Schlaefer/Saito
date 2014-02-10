<?php

	class EntryFixture extends CakeTestFixture {

		public $fields = array(
				'created' => array( 'type' => 'datetime', 'null' => true, 'default' => null, 'collate' => null, 'comment' => '' ),
				'modified' => array( 'type' => 'datetime', 'null' => true, 'default' => null, 'collate' => null, 'comment' => '' ),
				'id' => array( 'type' => 'integer', 'null' => false, 'default' => null, 'key' => 'primary', 'collate' => null, 'comment' => '' ),
				'pid' => array( 'type' => 'integer', 'null' => false, 'default' => '0', 'key' => 'index', 'collate' => null, 'comment' => '' ),
				'tid' => array( 'type' => 'integer', 'null' => false, 'default' => '0', 'key' => 'index', 'collate' => null, 'comment' => '' ),
				'uniqid' => array( 'type' => 'string', 'null' => true, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => '', 'charset' => 'utf8' ),
				'time' => array( 'type' => 'timestamp', 'null' => false, 'default' => 'CURRENT_TIMESTAMP', 'key' => 'index', 'collate' => null, 'comment' => '' ),
				'last_answer' => array( 'type' => 'timestamp', 'null' => false, 'default' => '0000-00-00 00:00:00', 'key' => 'index', 'collate' => null, 'comment' => '' ),
				'edited' => array( 'type' => 'timestamp', 'null' => false, 'default' => '0000-00-00 00:00:00', 'collate' => null, 'comment' => '' ),
				'edited_by' => array( 'type' => 'string', 'null' => true, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => '', 'charset' => 'utf8' ),
				'user_id' => array( 'type' => 'integer', 'null' => true, 'default' => '0', 'key' => 'index', 'collate' => null, 'comment' => '' ),
				'name' => array( 'type' => 'string', 'null' => true, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => '', 'charset' => 'utf8' ),
				'subject' => array( 'type' => 'string', 'null' => true, 'default' => null, 'key' => 'index', 'collate' => 'utf8_general_ci', 'comment' => '', 'charset' => 'utf8' ),
				'category' => array( 'type' => 'integer', 'null' => false, 'default' => '0', 'collate' => null, 'comment' => '' ),
				'text' => array( 'type' => 'text', 'null' => true, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => '', 'charset' => 'utf8' ),
				'email_notify' => array( 'type' => 'integer', 'null' => true, 'default' => '0', 'length' => 4, 'collate' => null, 'comment' => '' ),
				'locked' => array( 'type' => 'integer', 'null' => true, 'default' => '0', 'length' => 4, 'collate' => null, 'comment' => '' ),
				'fixed' => array( 'type' => 'integer', 'null' => true, 'default' => '0', 'length' => 4, 'collate' => null, 'comment' => '' ),
				'views' => array( 'type' => 'integer', 'null' => true, 'default' => '0', 'collate' => null, 'comment' => '' ),
				'flattr' => array( 'type' => 'boolean', 'null' => true, 'default' => null, 'collate' => null, 'comment' => '' ),
				'nsfw' => array( 'type' => 'boolean', 'null' => true, 'default' => null, 'collate' => null, 'comment' => '' ),
				'ip' => array( 'type' => 'string', 'null' => true, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => '', 'charset' => 'utf8' ),
				'reposts' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 4),
				'solves' => array( 'type' => 'integer', 'null' => false, 'default' => '0'),
		);

/**
 * 	- 1
 *  	- 2
 *    	- 3
 *    	- 9
 *      	- 7
 *  	- 8
 *
 * 	- 4
 * 		- 5
 *
 *	- 6
 *
 *  - 10
 *
 * @var type array
 */
		public $records = array(
			// thread 1
			// -------------------------------------
			[
				'id' => 1,
				'subject' => 'First_Subject',
				'text' => 'First_Text',
				'pid' => 0,
				'tid' => 1,
				'time' => '2000-01-01 20:00:00',
				'last_answer' => '2000-01-04 20:02:00',
				'category' => 2, // accession = 0
				'user_id' => 3,
				'locked' => 0
			],
			[
				'id' => 2,
				'subject' => 'Second_Subject',
				'text' => 'Second_Text',
				'pid' => 1,
				'tid' => 1,
				'time' => '2000-01-01 20:01:00',
				'last_answer' => '2000-01-01 20:01:00',
				'category' => 2,
				'user_id' => 2,
				'locked' => 0
			],
			[
				'id' => 3,
				'subject' => 'Third_Subject',
				'text' => '< Third_Text',
				'pid' => 2,
				'tid' => 1,
				'time' => '2000-01-01 20:02:00',
				'last_answer' => '2000-01-01 20:02:00',
				'category' => 2,
				'user_id' => 3,
				'name' => 'Ulysses',
				'edited' => '2000-01-01 20:04:00',
				'edited_by' => 'Ulysses',
				'ip' => '1.1.1.1',
				'locked' => 0
			],
			[
				'id' => 7,
				'subject' => 'Fouth_Subject',
				'text' => 'Fourth_Text',
				'pid' => 9,
				'tid' => 1,
				'time' => '2000-01-02 20:03:00',
				'last_answer' => '2000-01-02 20:03:00',
				'category' => 2,
				'user_id' => 3,
				'name' => 'Ulysses',
				'ip' => '1.1.1.1',
				'locked' => 0
			],
			[
				'id' => 8,
				'subject' => 'Fifth_Subject',
				'text' => 'Fifth_Text',
				'pid' => 1,
				'tid' => 1,
				'time' => '2000-01-03 20:02:00',
				'last_answer' => '2000-01-03 20:02:00',
				'category' => 2,
				'user_id' => 3,
				'name' => 'Ulysses',
				'ip' => '1.1.1.1',
				'locked' => 0
			],
			[
				'id' => 9,
				'subject' => 'Sixth_Subject',
				'text' => 'Sixth_Text',
				'pid' => 2,
				'tid' => 1,
				'time' => '2000-01-04 20:02:00',
				'last_answer' => '2000-01-04 20:02:00',
				'category' => 2,
				'user_id' => 3,
				'name' => 'Ulysses',
				'ip' => '1.1.1.1',
				'locked' => 0
			],
			// thread 2
			// -------------------------------------
			[
				'id' => 4,
				'subject' => 'Second Thread First_Subject',
				'text' => '',
				'pid' => 0,
				'tid' => 4,
				'time' => '2000-01-01 10:00:00',
				'last_answer' => '2000-01-04 20:02:00',
				'category' => 4, // accession = 1
				'user_id' => 1,
				'locked' => 1
			],
			[
				'id' => 5,
				'subject' => 'Second Thread Second_Subject',
				'text' => '',
				'pid' => 4,
				'tid' => 4,
				'time' => '2000-01-04 20:02:00',
				'last_answer' => '2000-01-04 20:02:00',
				'category' => 4,
				'user_id' => 3,
				'name' => 'Ulysses',
				'edited' => '0000-00-00 00:00:00',
				'edited_by' => null,
				'ip' => '1.1.1.1',
				'locked' => 1
			],
			// thread 3
			// -------------------------------------
			[
				'id' => 6,
				'subject' => 'Third Thread First_Subject',
				'text' => '',
				'pid' => 0,
				'tid' => 6,
				'time' => '2000-01-01 11:00:00',
				'last_answer' => '2000-01-01 11:00:00',
				'category' => 1, // accession = 2
				'user_id' => 1,
				'name' => 'Alice',
				'edited' => '0000-00-00 00:00:00',
				'edited_by' => null,
				'ip' => '1.1.1.3',
				'locked' => 0
			],
			// thread 4
			// -------------------------------------
			[
				'id' => 10,
				'subject' => 'First_Subject',
				'text' => "<script>alert('foo');<script>",
				'pid' => 0,
				'tid' => 10,
				'time' => '2000-01-01 10:59:00',
				'last_answer' => '2000-01-01 10:59:00',
				'edited' => '0000-00-00 00:00:00',
				'edited_by' => null,
				'category' => 2, // accession = 0
				'user_id' => 3,
				'locked' => 1
			]
		);

	}
