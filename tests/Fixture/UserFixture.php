<?php

namespace App\Test\Fixture;

use Authentication\PasswordHasher\DefaultPasswordHasher;
use Authentication\PasswordHasher\PasswordHasherFactory;
use Cake\TestSuite\Fixture\TestFixture;

class UserFixture extends TestFixture
{

    public $fields = [
        'id' => [
            'type' => 'integer',
            'null' => false,
            'default' => null,
            'unsigned' => false,
        ],
        'user_type' => [
            'type' => 'string',
            'null' => true,
            'default' => null,
        ],
        'username' => [
            'type' => 'string',
            'null' => true,
            'default' => null,
            'length' => 191,
        ],
        'user_real_name' => [
            'type' => 'string',
            'null' => true,
            'default' => null,
        ],
        'password' => [
            'type' => 'string',
            'null' => true,
            'default' => null,
        ],
        'user_email' => [
            'type' => 'string',
            'null' => true,
            'default' => null,
        ],
        'user_hp' => [
            'type' => 'string',
            'null' => true,
            'default' => null,
        ],
        'user_place' => [
            'type' => 'string',
            'null' => true,
            'default' => null,
        ],
        'signature' => [
            'type' => 'string',
            'null' => true,
            'default' => null,
        ],
        'profile' => [
            'type' => 'text',
            'null' => true,
            'default' => null,
        ],
        'entry_count' => [
            'type' => 'integer',
            'null' => false,
            'default' => '0',
            'unsigned' => false,
        ],
        'logins' => [
            'type' => 'integer',
            'null' => false,
            'default' => '0',
            'unsigned' => false,
        ],
        'last_login' => [
            'type' => 'timestamp',
            'null' => true,
            'default' => null,
        ],
        'registered' => [
            'type' => 'timestamp',
            'null' => true,
            'default' => null,
        ],
        'last_refresh' => [
            'type' => 'datetime',
            'null' => true,
            'default' => null,
        ],
        'last_refresh_tmp' => [
            'type' => 'datetime',
            'null' => true,
            'default' => null,
        ],
        'personal_messages' => [
            'type' => 'boolean',
            'null' => false,
            'default' => '1',
        ],
        'user_lock' => ['type' => 'boolean', 'null' => false, 'default' => '0'],
        'activate_code' => [
            'type' => 'integer',
            'null' => false,
            'default' => '0',
            'length' => 7,
            'unsigned' => false,
        ],
        'user_signatures_hide' => [
            'type' => 'boolean',
            'null' => false,
            'default' => '0',
        ],
        'user_signatures_images_hide' => [
            'type' => 'boolean',
            'null' => false,
            'default' => '0',
        ],
        'user_forum_refresh_time' => [
            'type' => 'integer',
            'null' => true,
            'default' => '0',
            'unsigned' => false,
        ],
        'user_automaticaly_mark_as_read' => [
            'type' => 'boolean',
            'null' => false,
            'default' => '1',
        ],
        'user_sort_last_answer' => [
            'type' => 'boolean',
            'null' => false,
            'default' => '1',
        ],
        'user_color_new_postings' => [
            'type' => 'string',
            'null' => true,
            'default' => null,
        ],
        'user_color_actual_posting' => [
            'type' => 'string',
            'null' => true,
            'default' => null,
        ],
        'user_color_old_postings' => [
            'type' => 'string',
            'null' => true,
            'default' => null,
        ],
        'user_theme' => [
            'type' => 'string',
            'null' => true,
            'default' => null,
        ],
        'slidetab_order' => [
            'type' => 'string',
            'null' => true,
            'default' => null,
            'length' => 512,
        ],
        'inline_view_on_click' => [
            'type' => 'boolean',
            'null' => false,
            'default' => '0',
        ],
        'user_show_thread_collapsed' => [
            'type' => 'boolean',
            'null' => false,
            'default' => '0',
        ],
        'user_category_override' => [
            'type' => 'boolean',
            'null' => false,
            'default' => '0',
        ],
        'user_category_active' => [
            'type' => 'integer',
            'null' => false,
            'default' => '0',
            'unsigned' => false,
        ],
        'user_category_custom' => [
            'type' => 'string',
            'null' => true,
            'default' => null,
            'length' => 512,
        ],
        'ignore_count' => [
            'type' => 'integer',
            'null' => false,
            'default' => '0',
            'length' => 10,
            'unsigned' => true,
        ],
        'avatar' => [
            'type' => 'string',
            'null' => true,
            'default' => null,
        ],
        'avatar_dir' => [
            'type' => 'string',
            'null' => true,
            'default' => null,
        ],
        '_constraints' => [
            'primary' => [
                'type' => 'primary',
                'columns' => ['id'],
            ],
            'username' => ['type' => 'unique', 'columns' => 'username'],
        ],
        '_options' => [
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci',
            'engine' => 'InnoDB',
        ],
    ];

    public $records = [
        [
            'id' => 1,
            'username' => 'Alice',
            'user_type' => 'admin', // !important
            'user_email' => 'alice@example.com',
        ],
        [
            'id' => 2,
            'username' => 'Mitch',
            'user_type' => 'mod', // !important
            'user_email' => 'mitch@example.com',
        ],
        [
            'id' => 3,
            'username' => 'Ulysses',
            'user_type' => 'user', //!important
            'user_email' => 'ulysses@example.com',
            'personal_messages' => 1,
            'last_login' => '2010-09-02 12:00',
        ],
        [
            'id' => 4,
            'username' => 'Change Password Test',
            'user_email' => 'cpw@example.com',
            'user_automaticaly_mark_as_read' => 1,
        ],
        [
            'id' => 5,
            'username' => 'Uma',
            'user_email' => 'uma@example.com',
            'user_automaticaly_mark_as_read' => 1,
        ],
        [
            'id' => 6,
            'username' => 'Second Admin',
            'user_type' => 'admin',
            'user_email' => 'second admin@example.com',
            //testtest
            'password' => '$2y$10$LxV1Ff181IBFQfHWNMfmCee9cu2YY.kPKc30Jftb05nBCsjw5T9pi',
            'user_automaticaly_mark_as_read' => 1,
            'registered' => '2010-09-01 11:12',
        ],
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
            'user_lock' => 1,
        ],
        [
            'id' => 9,
            'username' => 'Liane',
            'user_type' => 'user',
            'user_email' => 'liane@example.com',
            'password' => '098f6bcd4621d373cade4e832627b4f6', // outdated password
            'personal_messages' => 1,
        ],
        [
            'id' => 10,
            'username' => 'Diane',
            'user_email' => 'diane@example.com',
            'activate_code' => 1548,
        ],
        [
            'id' => 11,
            'username' => 'Oliver',
            'user_type' => 'owner',
            'user_email' => 'oliver@example.com',
        ],
    ];

    public function init()
    {
        $hasher = PasswordHasherFactory::build(DefaultPasswordHasher::class);
        $common = [
            'activate_code' => 0,
            'password' => $hasher->hash('test'),
            'personal_messages' => 0,
            'registered' => '2009-01-01 00:00',
            'slidetab_order' => null,
            'user_automaticaly_mark_as_read' => 0,
            'user_category_custom' => '',
            'user_lock' => 0,
            'user_type' => 'user',
        ];

        foreach ($this->records as $k => $record) {
            $this->records[$k] += $common;
        }

        return parent::init();
    }
}
