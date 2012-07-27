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
						'name' => 'quote_symbol',
						'value' => '>',
				),
				array(
						'name' => 'smilies',
						'value' => 1,
				),
				array(
						'name' => 'tos_enabled',
						'value' => 1,
				),
				array(
						'name' => 'tos_url',
						'value' => 'http://example.com/tos-url.html/',
				),
				array(
						'name' => 'topics_per_page',
						'value' => '20',
				),
				array(
						'name' => 'timezone',
						'value' => 'UTC',
				),
				array(
						'name' => 'block_user_ui',
						'value' => 1,
				),
				array(
						'name' => 'subject_maxlength',
						'value' => 100,
				),
		);

	}

?>