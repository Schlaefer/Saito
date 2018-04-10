<?php

	class UserFixture extends CakeTestFixture {

		public $fields = array(
			'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
			'user_type' => array('type' => 'string', 'null' => true, 'default' => null, 'collate' => 'utf8_unicode_ci', 'charset' => 'utf8'),
			'username' => array('type' => 'string', 'null' => true, 'default' => null, 'key' => 'unique', 'collate' => 'utf8_unicode_ci', 'charset' => 'utf8'),
			'user_real_name' => array('type' => 'string', 'null' => true, 'default' => null, 'collate' => 'utf8_unicode_ci', 'charset' => 'utf8'),
			'password' => array('type' => 'string', 'null' => true, 'default' => null, 'collate' => 'utf8_unicode_ci', 'charset' => 'utf8'),
			'user_email' => array('type' => 'string', 'null' => true, 'default' => null, 'collate' => 'utf8_unicode_ci', 'charset' => 'utf8'),
			'user_hp' => array('type' => 'string', 'null' => true, 'default' => null, 'collate' => 'utf8_unicode_ci', 'charset' => 'utf8'),
			'user_place' => array('type' => 'string', 'null' => true, 'default' => null, 'collate' => 'utf8_unicode_ci', 'charset' => 'utf8'),
			'user_place_lat' => array('type' => 'float', 'null' => true, 'default' => null, 'unsigned' => false),
			'user_place_lng' => array('type' => 'float', 'null' => true, 'default' => null, 'unsigned' => false),
			'user_place_zoom' => array('type' => 'integer', 'null' => true, 'default' => null, 'length' => 4, 'unsigned' => false),
			'signature' => array('type' => 'string', 'null' => true, 'default' => null, 'collate' => 'utf8_unicode_ci', 'charset' => 'utf8'),
			'profile' => array('type' => 'text', 'null' => true, 'default' => null, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
			'entry_count' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false),
			'logins' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false),
			'last_login' => array('type' => 'timestamp', 'null' => true, 'default' => null),
			'registered' => array('type' => 'timestamp', 'null' => true, 'default' => null),
			'last_refresh' => array('type' => 'datetime', 'null' => true, 'default' => null),
			'last_refresh_tmp' => array('type' => 'datetime', 'null' => true, 'default' => null),
			'personal_messages' => array('type' => 'boolean', 'null' => false, 'default' => '1'),
			'user_lock' => array('type' => 'boolean', 'null' => false, 'default' => '0'),
			'activate_code' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 7, 'unsigned' => false),
			'user_signatures_hide' => array('type' => 'boolean', 'null' => false, 'default' => '0'),
			'user_signatures_images_hide' => array('type' => 'boolean', 'null' => false, 'default' => '0'),
			'user_forum_refresh_time' => array('type' => 'integer', 'null' => true, 'default' => '0', 'unsigned' => false),
			'user_automaticaly_mark_as_read' => array('type' => 'boolean', 'null' => false, 'default' => '1'),
			'user_sort_last_answer' => array('type' => 'boolean', 'null' => false, 'default' => '1'),
			'user_color_new_postings' => array('type' => 'string', 'null' => true, 'default' => null, 'collate' => 'utf8_unicode_ci', 'charset' => 'utf8'),
			'user_color_actual_posting' => array('type' => 'string', 'null' => true, 'default' => null, 'collate' => 'utf8_unicode_ci', 'charset' => 'utf8'),
			'user_color_old_postings' => array('type' => 'string', 'null' => true, 'default' => null, 'collate' => 'utf8_unicode_ci', 'charset' => 'utf8'),
			'user_theme' => array('type' => 'string', 'null' => true, 'default' => null, 'collate' => 'utf8_unicode_ci', 'charset' => 'utf8'),
			'slidetab_order' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 512, 'collate' => 'utf8_unicode_ci', 'charset' => 'utf8'),
			'show_userlist' => array('type' => 'boolean', 'null' => false, 'default' => '0', 'comment' => 'stores if userlist is shown in front layout'),
			'show_recentposts' => array('type' => 'boolean', 'null' => false, 'default' => '0'),
			'show_recententries' => array('type' => 'boolean', 'null' => false, 'default' => '0'),
			'show_shoutbox' => array('type' => 'boolean', 'null' => false, 'default' => '0'),
			'inline_view_on_click' => array('type' => 'boolean', 'null' => false, 'default' => '0'),
			'user_show_thread_collapsed' => array('type' => 'boolean', 'null' => false, 'default' => '0'),
			'user_category_override' => array('type' => 'boolean', 'null' => false, 'default' => '0'),
			'user_category_active' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false),
			'user_category_custom' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 512, 'collate' => 'utf8_unicode_ci', 'charset' => 'utf8'),
			'ignore_count' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 10, 'unsigned' => true),
			'indexes' => array(
				'PRIMARY' => array('column' => 'id', 'unique' => 1),
				'username' => array('column' => 'username', 'unique' => 1)
			),
			'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_unicode_ci', 'engine' => 'InnoDB')
		);

		protected $_common = [
			'activate_code' => 0,
			// `test`
			'password' => '098f6bcd4621d373cade4e832627b4f6',
			'personal_messages' => 0,
			'registered' => '2009-01-01 00:00',
			'slidetab_order' => null,
			'user_automaticaly_mark_as_read' => 0,
			'user_category_custom' => '',
			'user_lock' => 0,
			'user_type' => 'user'
		];

		public $records = array(
			array(
				'id' => 1,
				'username' => 'Alice',
				'user_type' => 'admin',
				'user_email' => 'alice@example.com',
			),
			array(
				'id' => 2,
				'username' => 'Mitch',
				'user_type' => 'mod',
				'user_email' => 'mitch@example.com',
			),
			array(
				'id' => 3,
				'username' => 'Ulysses',
				'user_email' => 'ulysses@example.com',
				'personal_messages' => 1,
				'user_place_lat' => 21.610,
				'user_place_lng' => -158.096
			),
			array(
				'id' => 4,
				'username' => 'Change Password Test',
				'user_email' => 'cpw@example.com',
				'user_automaticaly_mark_as_read' => 1,
			),
			array(
				'id' => 5,
				'username' => 'Uma',
				'user_email' => 'uma@example.com',
				'user_automaticaly_mark_as_read' => 1,
			),
			array(
				'id' => 6,
				'username' => 'Second Admin',
				'user_type' => 'admin',
				'user_email' => 'second admin@example.com',
				//testtest
				'password' => '$2a$10$7d0000dd8a37f797acb53OY.oaPgJ2vV4PE3.VBpmsm9OM/zMlzNq',
				'user_automaticaly_mark_as_read' => 1,
				'registered' => '2010-09-01 11:12',
			),
			[
				'id' => 7,
				'username' => '&<Username',
				'user_email' => 'xss@example.com',
				'user_real_name' => '&<RealName',
				'user_hp' => '&<Homepage',
				'user_place' => '&<Place',
				'profile' => '&<Profile',
				'signature' => '&<Signature',
				'user_automaticaly_mark_as_read' => 1,
				'registered' => '2010-09-02 11:00',
			],
			[
				'id' => 8,
				'username' => 'Walt',
				'user_email' => 'walt@example.com',
				'user_lock' => 1
			],
			[
				'id' => 9,
				'username' => 'Liane',
				'user_email' => 'liane@example.com'
			],
			[
				'id' => 10,
				'username' => 'Diane',
				'user_email' => 'diane@example.com',
				'activate_code' => 1548
			],
		);

		public function init() {
			foreach ($this->records as $k => $record) {
				$this->records[$k] += $this->_common;
			}
			return parent::init();
		}

	}
