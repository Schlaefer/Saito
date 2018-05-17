<?php

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class UserBlockFixture extends TestFixture
{

    public $fields = [
        'id' => [
            'type' => 'integer',
            'null' => false,
            'default' => null,
            'unsigned' => true
        ],
        'created' => [
            'type' => 'datetime',
            'null' => true,
            'default' => null,
            'collate' => null,
            'comment' => ''
        ],
        'user_id' => [
            'type' => 'integer',
            'null' => false,
            'default' => null,
            'unsigned' => true
        ],
        'reason' => [
            'type' => 'string',
            'null' => true,
            'default' => null,
            'collate' => 'utf8_general_ci',
            'charset' => 'utf8'
        ],
        'blocked_by_user_id' => [
            'type' => 'integer',
            'null' => true,
            'default' => null,
            'unsigned' => true
        ],
        'ends' => ['type' => 'datetime', 'null' => true, 'default' => null],
        'ended' => ['type' => 'datetime', 'null' => true, 'default' => null],
        'hash' => [
            'type' => 'string',
            'null' => true,
            'default' => null,
            'length' => 32,
            'collate' => 'utf8_general_ci',
            'charset' => 'utf8'
        ],
        '_constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['id']]
        ],
        '_options' => [
            'charset' => 'utf8',
            'collate' => 'utf8_general_ci',
            'engine' => 'InnoDB'
        ],
    ];

    public $records = [
        [
            'id' => 1,
            'user_id' => 1,
            'blocked_by_user_id' => 1,
            'created' => '2014-08-10 08:59:43',
            'ends' => '2014-08-11 08:59:43',
            'ended' => null
        ],
        [
            'id' => 2,
            'user_id' => 2,
            'blocked_by_user_id' => 1,
            'created' => '2014-08-12 08:59:43',
            'ends' => null,
            'ended' => null
        ],
    ];
}
