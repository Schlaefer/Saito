<?php
/**
 * EsnotificationFixture
 *
 */
class EsnotificationFixture extends CakeTestFixture {

/**
 * Fields
 *
 * @var array
 */
	public $fields = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'key' => 'primary'),
		'user_id' => array('type' => 'integer', 'null' => false, 'default' => null),
		'esevent_id' => array('type' => 'integer', 'null' => false, 'default' => null),
		'esreceiver_id' => array('type' => 'integer', 'null' => false, 'default' => null),
		'created' => array( 'type' => 'datetime', 'null' => false, 'default' => NULL, 'collate' => NULL, 'comment' => '' ),
		'modified' => array( 'type' => 'datetime', 'null' => false, 'default' => NULL, 'collate' => NULL, 'comment' => '' ),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1)
		),
		'tableParameters' => array('charset' => 'latin1', 'collate' => 'latin1_general_ci', 'engine' => 'InnoDB')
	);

	/**
		 * Records
		 *
		 * @var array
		 */
		public $records = array(
				array(
						'id'						 => 1,
						'user_id'				 => 1,
						'esevent_id'		 => 1,
						'esreceiver_id'	 => 1,
				),
				array(
						'id'						 => 2,
						'user_id'				 => 1,
						'esevent_id'		 => 1,
						'esreceiver_id'	 => 2,
				),
				array(
						'id'						 => 3,
						'user_id'				 => 3,
						'esevent_id'		 => 1,
						'esreceiver_id'	 => 1,
				),
		);

	}
