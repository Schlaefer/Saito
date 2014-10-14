<?php

	class SmileyFixture extends CakeTestFixture {

		public $fields = array(
			'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
			'order' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 4, 'unsigned' => false),
			'icon' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 100, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
			'image' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 100, 'collate' => 'utf8_unicode_ci', 'charset' => 'utf8'),
			'title' => array('type' => 'string', 'null' => true, 'default' => null, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
			'indexes' => array(
				'PRIMARY' => array('column' => 'id', 'unique' => 1)
			),
			'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_unicode_ci', 'engine' => 'MyISAM')
		);

		public $records = [
			[
				'id' => 1,
				'order' => 2,
				'icon' => 'smile_icon.png',
				'image' => 'smile_image.png',
				'title' => 'Smile',
			],
			[
				'id' => 2,
				'order' => 1,
				'icon' => 'wink.svg',
				'image' => '',
				'title' => 'Wink',
			],
			[
				'id' => 3,
				'order' => 3,
				'icon' => 'coffee',
				'image' => '',
				'title' => 'Coffee',
			],
		];

	}

