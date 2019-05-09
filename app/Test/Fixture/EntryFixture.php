<?php

	class EntryFixture extends CakeTestFixture {

		protected $_common = [
			'edited' => null,
			'edited_by' => null,
			'locked' => 0,
			'solves' => 0
		];

		public $fields = [
				'created' => [
						'type' => 'datetime',
						'null' => true,
						'default' => null,
						'collate' => null,
						'comment' => ''
				],
				'modified' => [
						'type' => 'datetime',
						'null' => true,
						'default' => null,
						'collate' => null,
						'comment' => ''
				],
				'id' => [
						'type' => 'integer',
						'null' => false,
						'default' => null,
						'key' => 'primary',
						'collate' => null,
						'comment' => ''
				],
				'pid' => [
						'type' => 'integer',
						'null' => false,
						'default' => '0',
						'key' => 'index',
						'collate' => null,
						'comment' => ''
				],
				'tid' => [
						'type' => 'integer',
						'null' => false,
						'default' => '0',
						'key' => 'index',
						'collate' => null,
						'comment' => ''
				],
				'time' => [
						'type' => 'timestamp',
						'null' => false,
						'default' => 'CURRENT_TIMESTAMP',
						'key' => 'index',
						'collate' => null,
						'comment' => ''
				],
				'last_answer' => [
						'type' => 'timestamp',
						'null' => true,
						'default' => null,
						'key' => 'index',
						'collate' => null,
						'comment' => ''
				],
				'edited' => [
						'type' => 'timestamp',
						'null' => true,
						'default' => null,
						'collate' => null,
						'comment' => ''
				],
				'edited_by' => [
						'type' => 'string',
						'null' => true,
						'default' => null,
						'collate' => 'utf8_general_ci',
						'comment' => '',
						'charset' => 'utf8'
				],
				'user_id' => [
						'type' => 'integer',
						'null' => true,
						'default' => '0',
						'key' => 'index',
						'collate' => null,
						'comment' => ''
				],
				'name' => [
						'type' => 'string',
						'null' => true,
						'default' => null,
						'collate' => 'utf8_general_ci',
						'comment' => '',
						'charset' => 'utf8'
				],
				'subject' => [
						'type' => 'string',
						'null' => true,
						'default' => null,
						'key' => 'index',
						'collate' => 'utf8_general_ci',
						'comment' => '',
						'charset' => 'utf8'
				],
				'category_id' => [
						'type' => 'integer',
						'null' => false,
						'default' => '0',
						'collate' => null,
						'comment' => ''
				],
				'text' => [
						'type' => 'text',
						'null' => true,
						'default' => null,
						'collate' => 'utf8_general_ci',
						'comment' => '',
						'charset' => 'utf8'
				],
				'email_notify' => [
						'type' => 'integer',
						'null' => true,
						'default' => '0',
						'length' => 4,
						'collate' => null,
						'comment' => ''
				],
				'locked' => [
						'type' => 'integer',
						'null' => true,
						'default' => '0',
						'length' => 4,
						'collate' => null,
						'comment' => ''
				],
				'fixed' => [
						'type' => 'integer',
						'null' => true,
						'default' => '0',
						'length' => 4,
						'collate' => null,
						'comment' => ''
				],
				'views' => [
						'type' => 'integer',
						'null' => true,
						'default' => '0',
						'collate' => null,
						'comment' => ''
				],
				'flattr' => [
						'type' => 'boolean',
						'null' => true,
						'default' => null,
						'collate' => null,
						'comment' => ''
				],
				'ip' => [
						'type' => 'string',
						'null' => true,
						'default' => null,
						'collate' => 'utf8_general_ci',
						'comment' => '',
						'charset' => 'utf8'
				],
				'reposts' => [
						'type' => 'integer',
						'null' => false,
						'default' => '0',
						'length' => 4
				],
				'solves' => [
						'type' => 'integer',
						'null' => false,
						'default' => '0'
				],
				'tableParameters' => [
						'engine' => 'MyISAM',
						'charset' => 'utf8',
						'collate' => 'utf8_general_ci'
				]
		];

/**
 * 	- 1
 *  	- 2
 *    		- 3
 *    		- 9
 *      		- 7
 *  	- 8
 *
 * 	- 4
 * 		- 5
 *
 *	- 6
 *
 *  - 10
 *
 *  - 11
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
				'category_id' => 2, // accession = 0
				'user_id' => 3
			],
			[
				'id' => 2,
				'subject' => 'Second_Subject',
				'text' => 'Second_Text',
				'pid' => 1,
				'tid' => 1,
				'time' => '2000-01-01 20:01:00',
				'last_answer' => '2000-01-01 20:01:00',
				'category_id' => 2,
				'user_id' => 2
			],
			[
				'id' => 3,
				'subject' => 'Third_Subject',
				'text' => '< Third_Text',
				'pid' => 2,
				'tid' => 1,
				'time' => '2000-01-01 20:02:00',
				'last_answer' => '2000-01-01 20:02:00',
				'category_id' => 2,
				'user_id' => 3,
				'name' => 'Ulysses',
				'edited' => '2000-01-01 20:04:00',
				'edited_by' => 'Ulysses',
				'ip' => '1.1.1.1',
				'solves' => 1
			],
			[
				'id' => 7,
				'subject' => 'Fouth_Subject',
				'text' => 'Fourth_Text',
				'pid' => 9,
				'tid' => 1,
				'time' => '2000-01-02 20:03:00',
				'last_answer' => '2000-01-02 20:03:00',
				'category_id' => 2,
				'user_id' => 3,
				'name' => 'Ulysses',
				'ip' => '1.1.1.1',
				'solves' => 1
			],
			[
				'id' => 8,
				'subject' => 'Fifth_Subject',
				'text' => 'Fifth_Text',
				'pid' => 1,
				'tid' => 1,
				'time' => '2000-01-03 20:02:00',
				'last_answer' => '2000-01-03 20:02:00',
				'category_id' => 2,
				'user_id' => 3,
				'name' => 'Ulysses',
				'ip' => '1.1.1.1'
			],
			[
				'id' => 9,
				'subject' => 'Sixth_Subject',
				'text' => 'Sixth_Text',
				'pid' => 2,
				'tid' => 1,
				'time' => '2000-01-04 20:02:00',
				'last_answer' => '2000-01-04 20:02:00',
				'category_id' => 2,
				'user_id' => 3,
				'name' => 'Ulysses',
				'ip' => '1.1.1.1'
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
				'category_id' => 4, // accession = 1
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
				'category_id' => 4,
				'user_id' => 3,
				'name' => 'Ulysses',
				'ip' => '1.1.1.1',
				'locked' => 1,
				'solves' => 4
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
				'category_id' => 1, // accession = 2
				'user_id' => 1,
				'name' => 'Alice',
				'ip' => '1.1.1.3'
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
				'category_id' => 2, // accession = 0
				'user_id' => 3,
				'locked' => 1
			],
			// thread 5
			// -------------------------------------
			[
			'id' => 11,
				'subject' => '&<Subject',
				'text' => "&<Text",
				'pid' => 0,
				'tid' => 11,
				'time' => '2000-01-01 10:59:00',
				'last_answer' => '2000-01-01 10:59:00',
				'category_id' => 2, // accession = 0
				'user_id' => 7
			]
		);

		public function init() {
			foreach ($this->records as $k => $record) {
				$this->records[$k] += $this->_common;
			}
			return parent::init();
		}

	}
