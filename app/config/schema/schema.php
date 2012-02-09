<?php 
/* App schema generated on: 2012-02-09 10:17:58 : 1328779078*/
class AppSchema extends CakeSchema {
	var $name = 'App';

	function before($event = array()) {
		return true;
	}

	function after($event = array()) {
	}

	var $categories = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'key' => 'primary'),
		'category_order' => array('type' => 'integer', 'null' => false, 'default' => '0'),
		'category' => array('type' => 'string', 'null' => true, 'default' => NULL, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'description' => array('type' => 'string', 'null' => true, 'default' => NULL, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'accession' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 4),
		'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1)),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_unicode_ci', 'engine' => 'MyISAM')
	);
	var $entries = array(
		'created' => array('type' => 'datetime', 'null' => false, 'default' => NULL),
		'modified' => array('type' => 'datetime', 'null' => false, 'default' => NULL),
		'id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'key' => 'primary'),
		'pid' => array('type' => 'integer', 'null' => false, 'default' => '0', 'key' => 'index'),
		'tid' => array('type' => 'integer', 'null' => false, 'default' => '0', 'key' => 'index'),
		'uniqid' => array('type' => 'string', 'null' => true, 'default' => NULL, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'time' => array('type' => 'timestamp', 'null' => false, 'default' => 'CURRENT_TIMESTAMP', 'key' => 'index'),
		'last_answer' => array('type' => 'timestamp', 'null' => false, 'default' => '0000-00-00 00:00:00', 'key' => 'index'),
		'edited' => array('type' => 'timestamp', 'null' => false, 'default' => '0000-00-00 00:00:00'),
		'edited_by' => array('type' => 'string', 'null' => true, 'default' => NULL, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'user_id' => array('type' => 'integer', 'null' => true, 'default' => '0', 'key' => 'index'),
		'name' => array('type' => 'string', 'null' => true, 'default' => NULL, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'subject' => array('type' => 'string', 'null' => true, 'default' => NULL, 'key' => 'index', 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'category' => array('type' => 'integer', 'null' => false, 'default' => '0'),
		'text' => array('type' => 'text', 'null' => true, 'default' => NULL, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'email_notify' => array('type' => 'integer', 'null' => true, 'default' => '0', 'length' => 4),
		'locked' => array('type' => 'integer', 'null' => true, 'default' => '0', 'length' => 4),
		'fixed' => array('type' => 'integer', 'null' => true, 'default' => '0', 'length' => 4),
		'views' => array('type' => 'integer', 'null' => true, 'default' => '0'),
		'flattr' => array('type' => 'boolean', 'null' => true, 'default' => NULL),
		'nsfw' => array('type' => 'boolean', 'null' => true, 'default' => NULL),
		'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1), 'tid' => array('column' => 'tid', 'unique' => 0), 'user_id' => array('column' => 'user_id', 'unique' => 0), 'last_answer' => array('column' => 'last_answer', 'unique' => 0), 'pft' => array('column' => array('pid', 'fixed', 'time', 'category'), 'unique' => 0), 'pfl' => array('column' => array('pid', 'fixed', 'last_answer', 'category'), 'unique' => 0), 'pid_category' => array('column' => array('pid', 'category'), 'unique' => 0), 'user_id-time' => array('column' => array('time', 'user_id'), 'unique' => 0), 'fulltext_search' => array('column' => array('subject', 'name', 'text'), 'unique' => 0, 'type' => 'fulltext')),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'MyISAM')
	);
	var $settings = array(
		'name' => array('type' => 'string', 'null' => true, 'default' => NULL, 'collate' => 'utf8_general_ci', 'charset' => 'utf8', 'key' => 'primary'),
		'value' => array('type' => 'string', 'null' => true, 'default' => NULL, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'indexes' => array(),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_unicode_ci', 'engine' => 'MyISAM')
	);
	var $smiley_codes = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'key' => 'primary'),
		'smiley_id' => array('type' => 'integer', 'null' => false, 'default' => '0'),
		'code' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 32, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1)),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_unicode_ci', 'engine' => 'MyISAM')
	);
	var $smilies = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'key' => 'primary'),
		'order' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 4),
		'icon' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 100, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'image' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 100, 'collate' => 'utf8_unicode_ci', 'charset' => 'utf8'),
		'title' => array('type' => 'string', 'null' => true, 'default' => NULL, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1)),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_unicode_ci', 'engine' => 'MyISAM')
	);
	var $uploads = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'key' => 'primary'),
		'name' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 200, 'collate' => 'utf8_unicode_ci', 'charset' => 'utf8'),
		'type' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 200, 'collate' => 'utf8_unicode_ci', 'charset' => 'utf8'),
		'size' => array('type' => 'integer', 'null' => true, 'default' => NULL),
		'created' => array('type' => 'datetime', 'null' => true, 'default' => NULL),
		'modified' => array('type' => 'datetime', 'null' => true, 'default' => NULL),
		'user_id' => array('type' => 'integer', 'null' => true, 'default' => NULL),
		'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1)),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_unicode_ci', 'engine' => 'MyISAM')
	);
	var $useronline = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'key' => 'primary'),
		'created' => array('type' => 'datetime', 'null' => true, 'default' => NULL),
		'modified' => array('type' => 'datetime', 'null' => true, 'default' => NULL),
		'time' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 14),
		'user_id' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 32, 'key' => 'primary', 'collate' => 'utf8_unicode_ci', 'charset' => 'utf8'),
		'logged_in' => array('type' => 'boolean', 'null' => false, 'default' => NULL, 'key' => 'index'),
		'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1), 'user_id' => array('column' => 'user_id', 'unique' => 0), 'logged_in' => array('column' => 'logged_in', 'unique' => 0)),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_unicode_ci', 'engine' => 'MEMORY')
	);
	var $users = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'key' => 'primary'),
		'user_type' => array('type' => 'string', 'null' => true, 'default' => NULL, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'username' => array('type' => 'string', 'null' => true, 'default' => NULL, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'user_real_name' => array('type' => 'string', 'null' => true, 'default' => NULL, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'password' => array('type' => 'string', 'null' => true, 'default' => NULL, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'user_email' => array('type' => 'string', 'null' => true, 'default' => NULL, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'hide_email' => array('type' => 'integer', 'null' => true, 'default' => '0', 'length' => 4),
		'user_hp' => array('type' => 'string', 'null' => true, 'default' => NULL, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'user_place' => array('type' => 'string', 'null' => true, 'default' => NULL, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'signature' => array('type' => 'string', 'null' => true, 'default' => NULL, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'profile' => array('type' => 'text', 'null' => true, 'default' => NULL, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'entry_count' => array('type' => 'integer', 'null' => false, 'default' => '0'),
		'logins' => array('type' => 'integer', 'null' => false, 'default' => '0'),
		'last_login' => array('type' => 'timestamp', 'null' => false, 'default' => '0000-00-00 00:00:00'),
		'last_logout' => array('type' => 'timestamp', 'null' => false, 'default' => '0000-00-00 00:00:00'),
		'registered' => array('type' => 'timestamp', 'null' => false, 'default' => '0000-00-00 00:00:00'),
		'last_refresh' => array('type' => 'datetime', 'null' => false, 'default' => '0000-00-00 00:00:00'),
		'last_refresh_tmp' => array('type' => 'datetime', 'null' => false, 'default' => '0000-00-00 00:00:00'),
		'user_view' => array('type' => 'string', 'null' => true, 'default' => NULL, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'new_posting_notify' => array('type' => 'integer', 'null' => true, 'default' => '0', 'length' => 4),
		'new_user_notify' => array('type' => 'integer', 'null' => true, 'default' => '0', 'length' => 4),
		'personal_messages' => array('type' => 'integer', 'null' => true, 'default' => '0', 'length' => 4),
		'time_difference' => array('type' => 'integer', 'null' => true, 'default' => '0', 'length' => 4),
		'user_lock' => array('type' => 'integer', 'null' => true, 'default' => '0', 'length' => 4),
		'pwf_code' => array('type' => 'string', 'null' => true, 'default' => NULL, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'activate_code' => array('type' => 'string', 'null' => true, 'default' => NULL, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'user_font_size' => array('type' => 'string', 'null' => true, 'default' => NULL, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'user_signatures_hide' => array('type' => 'integer', 'null' => true, 'default' => '0', 'length' => 4),
		'user_signatures_images_hide' => array('type' => 'integer', 'null' => true, 'default' => '0', 'length' => 4),
		'user_categories' => array('type' => 'string', 'null' => true, 'default' => NULL, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'user_forum_refresh_time' => array('type' => 'integer', 'null' => true, 'default' => '0'),
		'user_forum_hr_ruler' => array('type' => 'integer', 'null' => true, 'default' => '0', 'length' => 4),
		'user_automaticaly_mark_as_read' => array('type' => 'integer', 'null' => true, 'default' => '1', 'length' => 4),
		'user_sort_last_answer' => array('type' => 'integer', 'null' => true, 'default' => '0', 'length' => 4),
		'user_color_new_postings' => array('type' => 'string', 'null' => true, 'default' => NULL, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'user_color_actual_posting' => array('type' => 'string', 'null' => true, 'default' => NULL, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'user_color_old_postings' => array('type' => 'string', 'null' => true, 'default' => NULL, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'user_show_own_signature' => array('type' => 'integer', 'null' => true, 'default' => '0', 'length' => 4),
		'slidetab_order' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 512, 'collate' => 'utf8_unicode_ci', 'charset' => 'utf8'),
		'show_userlist' => array('type' => 'boolean', 'null' => false, 'default' => '0', 'comment' => 'stores if userlist is shown in front layout'),
		'show_recentposts' => array('type' => 'boolean', 'null' => false, 'default' => '0'),
		'show_about' => array('type' => 'boolean', 'null' => false, 'default' => '0'),
		'show_donate' => array('type' => 'boolean', 'null' => false, 'default' => '0'),
		'inline_view_on_click' => array('type' => 'boolean', 'null' => false, 'default' => '0'),
		'flattr_uid' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 24, 'collate' => 'utf8_unicode_ci', 'charset' => 'utf8'),
		'flattr_allow_user' => array('type' => 'boolean', 'null' => true, 'default' => NULL),
		'flattr_allow_posting' => array('type' => 'boolean', 'null' => true, 'default' => NULL),
		'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1)),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_unicode_ci', 'engine' => 'MyISAM')
	);
}
?>