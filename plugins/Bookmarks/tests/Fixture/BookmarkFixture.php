<?php

declare(strict_types=1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace Bookmarks\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class BookmarkFixture extends TestFixture
{

    /**
     * Fields
     *
     * @var array
     */
    public $fields = [
        'id' => [
            'type' => 'integer',
            'null' => false,
            'default' => null,
            'unsigned' => false
        ],
        'user_id' => [
            'type' => 'integer',
            'null' => false,
            'default' => null,
            'unsigned' => false
        ],
        'entry_id' => [
            'type' => 'integer',
            'null' => false,
            'default' => null,
            'unsigned' => false
        ],
        'comment' => [
            'type' => 'string',
            'null' => true,
            'default' => null,
            'limit' => 255,
        ],
        'created' => ['type' => 'datetime', 'null' => true, 'default' => null],
        'modified' => ['type' => 'datetime', 'null' => true, 'default' => null],
        '_constraints' => [
            'primary' => [
                'type' => 'primary',
                'columns' => ['id']
            ]
        ],
        '_options' => [
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci',
            'engine' => 'InnoDB',
        ]
    ];

    /**
     * Records
     *
     * @var array
     */
    public $records = [
        [
            'id' => 1, // !important
            'user_id' => 3, // !important
            'entry_id' => 1, // !important
            'comment' => '', // !important
            'created' => '2012-08-07 09:51:45',
            'modified' => '2012-08-07 09:51:45'
        ],
        [
            'id' => 2,
            'user_id' => 3,
            'entry_id' => 11,
            'comment' => '< Comment 2',
            'created' => '2012-08-07 19:51:45',
            'modified' => '2012-08-07 19:51:45'
        ],
        [
            'id' => 3,
            'user_id' => 1,
            'entry_id' => 1,
            'comment' => 'Comment 3',
            'created' => '2012-08-07 09:51:45',
            'modified' => '2012-08-07 09:51:45'
        ],
        [
            'id' => 4,
            'user_id' => 2,
            'entry_id' => 4,
            'comment' => 'Comment 4',
            'created' => '2012-08-07 09:51:45',
            'modified' => '2012-08-07 09:51:45'
        ],
        [
            'id' => 5,
            'user_id' => 3,
            'entry_id' => 6,
            'comment' => '<BookmarkComment',
            'created' => '2012-08-07 09:51:45',
            'modified' => '2012-08-07 09:51:45'
        ]
    ];
}
