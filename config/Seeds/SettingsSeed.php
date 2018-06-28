<?php
use Migrations\AbstractSeed;

/**
 * Settings seed.
 */
class SettingsSeed extends AbstractSeed
{
    /**
     * Run Method.
     *
     * Write your database seeder using this method.
     *
     * More information on writing seeds is available here:
     * http://docs.phinx.org/en/latest/seeding.html
     *
     * @return void
     */
    public function run()
    {
        $data = [
            ['name' => 'autolink', 'value' => '1'],
            ['name' => 'bbcode_img', 'value' => '1'],
            ['name' => 'block_user_ui', 'value' => '1'],
            ['name' => 'db_version', 'value' => null],
            ['name' => 'category_chooser_global', 'value' => '0'],
            ['name' => 'category_chooser_user_override', 'value' => '1'],
            ['name' => 'edit_delay', 'value' => '3'],
            ['name' => 'edit_period', 'value' => '20'],
            ['name' => 'email_contact', 'value' => ''],
            ['name' => 'email_register', 'value' => ''],
            ['name' => 'email_system', 'value' => ''],
            ['name' => 'forum_disabled', 'value' => '0'],
            ['name' => 'forum_disabled_text', 'value' => 'We\'ll back soon'],
            ['name' => 'forum_email', 'value' => ''],
            ['name' => 'forum_name', 'value' => 'Saito Forum'],
            ['name' => 'quote_symbol', 'value' => '>'],
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
            [
                    'name' => 'video_domains_allowed',
                    'value' => 'youtube | youtube-nocookie | vimeo | vine'
            ]
        ];

        $table = $this->table('settings');
        $table->insert($data)->save();
    }
}
