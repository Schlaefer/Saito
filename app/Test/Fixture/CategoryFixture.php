<?php

	class CategoryFixture extends CakeTestFixture {

		public $fields = array(
				'id' => array( 'type' => 'integer', 'null' => false, 'default' => null, 'key' => 'primary' ),
				'category_order' => array( 'type' => 'integer', 'null' => false, 'default' => '0' ),
				'category' => array( 'type' => 'string', 'null' => true, 'default' => null ),
				'description' => array( 'type' => 'string', 'null' => true, 'default' => null ),
				'accession' => array( 'type' => 'integer', 'null' => false, 'default' => '0', 'length' => 4 ),
				'thread_count' => array( 'type' => 'integer', 'null' => false, 'default' => 0, 'length' => 11 ),
				'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1)),
				'tableParameters' => array()
		);

		public $records = array(
			array(
				'id' => 1,
				'category_order' => 1,
				'category' => 'Admin',
				'description' => '',
				'accession' => 2,
				'thread_count' => 1,
			),
			array(
				'id' => 2,
				'category_order' => 3,
				'category' => 'Ontopic',
				'description' => '',
				'accession' => 0,
				'thread_count' => 4,
			),
			array(
				'id' => 3,
				'category_order' => 2,
				'category' => 'Another Ontopic',
				'description' => '',
				'accession' => 0,
				'thread_count' => 0,
			),
			array(
				'id' => 4,
				'category_order' => 4,
				'category' => 'Offtopic',
				'description' => '',
				'accession' => 1,
				'thread_count' => 1,
			),
			array(
				'id' => 5,
				'category_order' => 4,
				'category' => 'Trash',
				'description' => '',
				'accession' => 1,
				'thread_count' => 0,
			),
		);

	}
