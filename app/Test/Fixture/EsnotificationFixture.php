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
		'deactivate' => array('type' => 'integer', 'null' => false, 'default' => 0),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1)
		),
//		'tableParameters' => array('charset' => 'latin1', 'collate' => 'latin1_general_ci', 'engine' => 'InnoDB')
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
						'deactivate'		 => 1234,
				),
				array(
						'id'						 => 2,
						'user_id'				 => 1,
						'esevent_id'		 => 1,
						'esreceiver_id'	 => 2,
						'deactivate'		 => 2234,
				),
				array(
						'id'						 => 3,
						'user_id'				 => 3,
						'esevent_id'		 => 1,
						'esreceiver_id'	 => 1,
						'deactivate'		 => 3234,
				),
				array(
						'id'						 => 4,
						'user_id'				 => 3,
						'esevent_id'		 => 4,
						'esreceiver_id'	 => 1,
						'deactivate'		 => 4234,
				),
				array(
						'id'						 => 5,
						'user_id'				 => 2,
						'esevent_id'		 => 4,
						'esreceiver_id'	 => 1,
						'deactivate'		 => 5234,
				),
				array(
						'id'						 => 6,
						'user_id'				 => 2,
						'esevent_id'		 => 2,
						'esreceiver_id'	 => 1,
						'deactivate'		 => 6234,
				),
				array(
						'id'						 => 7,
						'user_id'				 => 4,
						'esevent_id'		 => 3,
						'esreceiver_id'	 => 1,
						'deactivate'		 => 7234,
				),
		);

	}
