<?php
class SettingData {

	public $table = 'settings';
	public $primaryKey = 'name';

	var $records = array(
		array(
				'name'	=> 'autolink',
				'value'	=>	'1',
		),
		array(
				'name'	=> 'bbcode_img',
				'value'	=> '1',
		),
		array(
				'name'	=> 'edit_delay',
				'value'	=> '3',
			),
		array(
				'name'	=> 'edit_period',
				'value'	=> '20',
			),
		array(
				'name'	=> 'video_domains_allowed',
				'value'	=> 'youtube | vimeo',
			),
		array(
				'name'	=> 'flattr_category',
				'value'	=> 'text',
			),
		array(
				'name'	=> 'flattr_enabled',
				'value'	=> '1',
			),
		array(
				'name'	=> 'flattr_language',
				'value'	=> 'de_DE',
			),
		array(
				'name'	=> 'forum_disabled',
				'value'	=> '0',
			),
		array(
				'name'	=> 'forum_disabled_text',
				'value'	=> 'We\'ll back soon',
			),
		array(
				'name'	=> 'forum_email',
				'value'	=> '',
			),
		array(
				'name'	=> 'forum_name',
				'value'	=> '',
			),
		array(
				'name'	=> 'quote_symbol',
				'value'	=> '»',
			),
		array(
				'name'	=> 'signature_separator',
				'value'	=> '---',
			),
		array(
				'name'	=> 'smilies',
				'value'	=> '1',
			),
		array(
				'name'	=> 'subject_maxlength',
				'value'	=> '75',
			),
		array(
				'name'	=> 'text_word_maxlength',
				'value'	=> '120',
			),
		array(
				'name'	=> 'thread_depth_indent',
				'value'	=> '25',
			),
		array(
				'name'	=> 'topics_per_page',
				'value'	=> '20',
			),
		array(
				'name'	=> 'upload_max_img_size',
				'value'	=> '300',
			),
		array(
				'name'	=> 'upload_max_number_of_uploads',
				'value'	=> '10',
			),
		array(
				'name'	=> 'userranks_ranks',
				'value' => '100=Rookie|101=Veteran',
		),
		array(
				'name'	=> 'userranks_show',
				'value'	=> '1',
			),
		array(
				'name'	=> 'timezone',
				'value'	=> 'UTC',
			),
	);
}
?>