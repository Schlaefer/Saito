<?php

	class SettingFixture extends CakeTestFixture {

		public $fields = array(
				'name' => array( 'type' => 'string', 'null' => true, 'default' => NULL ),
				'value' => array( 'type' => 'string', 'null' => true, 'default' => NULL ),
				'indexes' => array( ),
				'tableParameters' => array( )
		);

		public $records = array(
				array(
						'name' => 'forum_name',
						'value' => 'macnemo'
				),
				array(
						'name' => 'autolink',
						'value' => '1',
				),
				array(
						'name' => 'userranks_ranks',
						'value' => '10=Castaway|20=Other|30=Dharma|100=Jacob',
				),
				array(
						'name' => 'smilies',
						'value' => 1,
				),
				array(
						'name' => 'topics_per_page',
						'value' => '20',
				)
		);

	}

?>