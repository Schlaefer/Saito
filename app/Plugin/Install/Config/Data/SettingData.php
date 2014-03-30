<?php
class SettingData {

	public $table = 'settings';
	public $primaryKey = 'name';

	var $records = [
			['name' => 'api_crossdomain', 'value' => ''],
			['name' => 'api_enabled', 'value' => '1'],
			['name' => 'autolink', 'value' => '1'],
			['name' => 'bbcode_img', 'value' => '1'],
			['name' => 'block_user_ui', 'value' => '1'],
			['name' => 'category_chooser_global', 'value' => '0'],
			['name' => 'category_chooser_user_override', 'value' => '1'],
			['name' => 'edit_delay', 'value' => '3'],
			['name' => 'edit_period', 'value' => '20'],
			['name' => 'embedly_enabled', 'value' => '0'],
			['name' => 'embedly_key', 'value' => ''],
			['name' => 'flattr_category', 'value' => 'text'],
			['name' => 'flattr_enabled', 'value' => '1'],
			['name' => 'flattr_language', 'value' => 'de_DE'],
			['name' => 'forum_disabled', 'value' => '0'],
			['name' => 'forum_disabled_text', 'value' => 'We\'ll back soon'],
			['name' => 'forum_email', 'value' => ''],
			['name' => 'forum_name', 'value' => ''],
			['name' => 'quote_symbol', 'value' => '»'],
			['name' => 'shoutbox_enabled', 'value' => '1'],
			['name' => 'shoutbox_max_shouts', 'value' => '10'],
			['name' => 'signature_separator', 'value' => '⁂'],
			['name' => 'smilies', 'value' => '1'],
			['name' => 'stopwatch_get', 'value' => '0'],
			['name' => 'store_ip', 'value' => '0'],
			['name' => 'store_ip_anonymized', 'value' => '1'],
			['name' => 'subject_maxlength', 'value' => '75'],
			['name' => 'text_word_maxlength', 'value' => '120'],
			['name' => 'thread_depth_indent', 'value' => '25'],
			['name' => 'timezone', 'value' => 'UTC'],
			['name' => 'topics_per_page', 'value' => '20'],
			['name' => 'tos_enabled', 'value' => '0'],
			['name' => 'tos_url', 'value' => ''],
			['name' => 'upload_max_img_size', 'value' => '300'],
			['name' => 'upload_max_number_of_uploads', 'value' => '10'],
			['name' => 'userranks_ranks', 'value' => '100=Rookie|101=Veteran'],
			['name' => 'userranks_show', 'value' => '1'],
			[
					'name' => 'video_domains_allowed',
					'value' => 'youtube | youtube-nocookie | vimeo'
			]
	];
}
?>