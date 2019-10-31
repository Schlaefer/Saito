<?php

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class SettingFixture extends TestFixture
{
    public $fields = [
        'id' => ['type' => 'integer', 'null' => false],
        'name' => ['type' => 'string', 'null' => true, 'default' => null],
        'value' => ['type' => 'string', 'null' => true, 'default' => null],
        '_options' => [
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci',
            'engine' => 'InnoDB'
        ]
    ];

    public $records = [
        ['id' => '1', 'name' => 'autolink', 'value' => '1'],
        ['id' => '3', 'name' => 'db_version', 'value' => null],
        ['id' => '4', 'name' => 'edit_delay', 'value' => '3'],
        ['id' => '5', 'name' => 'edit_period', 'value' => '20'],
        ['id' => '6', 'name' => 'forum_email', 'value' => 'forum_email@example.com'],
        ['id' => '7', 'name' => 'email_contact', 'value' => 'contact@example.com'],
        ['id' => '8', 'name' => 'email_register', 'value' => 'register@example.com'],
        ['id' => '9', 'name' => 'email_system', 'value' => 'system@example.com'],
        ['id' => '10', 'name' => 'forum_name', 'value' => 'macnemo'],
        ['id' => '11', 'name' => 'quote_symbol', 'value' => '>'],
        ['id' => '12', 'name' => 'smilies', 'value' => '1'],
        ['id' => '13', 'name' => 'subject_maxlength', 'value' => '40'],
        ['id' => '14', 'name' => 'thread_depth_indent', 'value' => '25'],
        ['id' => '15', 'name' => 'timezone', 'value' => 'UTC'],
        ['id' => '16', 'name' => 'topics_per_page', 'value' => '20'],
        ['id' => '17', 'name' => 'tos_enabled', 'value' => '1'],
        ['id' => '18', 'name' => 'tos_url', 'value' => 'http://example.com/tos-url.html/'],
        ['id' => '19', 'name' => 'category_chooser_global', 'value' => '0'],
        ['id' => '20', 'name' => 'category_chooser_user_override', 'value' => '1'],
        ['id' => '21', 'name' => 'category_chooser_user_override', 'value' => '1'],
        ['id' => '24', 'name' => 'forum_disabled', 'value' => '0'],
        ['id' => '24', 'name' => 'forum_disabled_text', 'value' => 'We\'ll back soon'],
        ['id' => '25', 'name' => 'store_ip', 'value' => '0'],
        ['id' => '26', 'name' => 'store_ip_anonymized', 'value' => '1'],
        ['id' => '27', 'name' => 'bbcode_img', 'value' => '1'],
        ['id' => '28', 'name' => 'signature_separator', 'value' => '⁂'],
        ['id' => '29', 'name' => 'text_word_maxlength', 'value' => '120'],
        [
                'id' => '30',
                'name' => 'video_domains_allowed',
                'value' => 'youtube | youtube-nocookie | vimeo | vine',
        ],
        ['id' => '31', 'name' => 'stopwatch_get', 'value' => '0'],
        ['id' => '32', 'name' => 'content_embed_active', 'value' => '1'],
        ['id' => '33', 'name' => 'content_embed_media', 'value' => '1'],
        ['id' => '34', 'name' => 'content_embed_text', 'value' => '1'],
    ];
}
