<?php

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
            'null' => false,
            'collate' => 'utf8_general_ci',
            'charset' => 'utf8'
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
            'charset' => 'utf8',
            'collate' => 'utf8_unicode_ci',
            'engine' => 'MyISAM'
        ]
    ];

    /**
     * Records
     *
     * @var array
     */
    public $records = array(
        array(
            'id' => 1,
            'user_id' => 3,
            'entry_id' => 1,
            'comment' => '',
            'created' => '2012-08-07 09:51:45',
            'modified' => '2012-08-07 09:51:45'
        ),
        array(
            'id' => 2,
            'user_id' => 3,
            'entry_id' => 11,
            'comment' => '< Comment 2',
            'created' => '2012-08-07 19:51:45',
            'modified' => '2012-08-07 19:51:45'
        ),
        array(
            'id' => 3,
            'user_id' => 1,
            'entry_id' => 1,
            'comment' => 'Comment 3',
            'created' => '2012-08-07 09:51:45',
            'modified' => '2012-08-07 09:51:45'
        ),
        array(
            'id' => 4,
            'user_id' => 2,
            'entry_id' => 4,
            'comment' => 'Comment 4',
            'created' => '2012-08-07 09:51:45',
            'modified' => '2012-08-07 09:51:45'
        ),
        [
            'id' => 5,
            'user_id' => 3,
            'entry_id' => 6,
            'comment' => '<BookmarkComment',
            'created' => '2012-08-07 09:51:45',
            'modified' => '2012-08-07 09:51:45'
        ]
    );
}
