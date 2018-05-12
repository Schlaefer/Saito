<?php

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class SettingFixture extends TestFixture
{

    public $fields = [
        'name' => ['type' => 'string', 'null' => true, 'default' => null],
        'value' => ['type' => 'string', 'null' => true, 'default' => null],
        '_options' => [
            'charset' => 'utf8',
            'collate' => 'utf8_unicode_ci',
            'engine' => 'MyISAM'
        ]
    ];

    public $records = [
        ['name' => 'autolink', 'value' => '1'],
        ['name' => 'block_user_ui', 'value' => '1'],
        ['name' => 'edit_delay', 'value' => '3'],
        ['name' => 'edit_period', 'value' => '20'],
        ['name' => 'forum_email', 'value' => 'forum_email@example.com'],
        ['name' => 'email_contact', 'value' => 'contact@example.com'],
        ['name' => 'email_register', 'value' => 'register@example.com'],
        ['name' => 'email_system', 'value' => 'system@example.com'],
        ['name' => 'forum_name', 'value' => 'macnemo'],
        ['name' => 'map_enabled', 'value' => '0'],
        ['name' => 'quote_symbol', 'value' => '>'],
        ['name' => 'smilies', 'value' => 1],
        ['name' => 'subject_maxlength', 'value' => 40],
        ['name' => 'thread_depth_indent', 'value' => 25],
        ['name' => 'timezone', 'value' => 'UTC'],
        ['name' => 'topics_per_page', 'value' => '20'],
        ['name' => 'tos_enabled', 'value' => 1],
        ['name' => 'tos_url', 'value' => 'http://example.com/tos-url.html/'],
        ['name' => 'category_chooser_global', 'value' => '0'],
        ['name' => 'category_chooser_user_override', 'value' => '1'],
        ['name' => 'category_chooser_user_override', 'value' => '1'],
        ['name' => 'upload_max_img_size', 'value' => '1500'],
        ['name' => 'upload_max_number_of_uploads', 'value' => '10'],
    ];
}
