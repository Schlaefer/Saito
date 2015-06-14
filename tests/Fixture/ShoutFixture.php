<?php

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class ShoutFixture extends TestFixture
{

    public $fields = array(
        'id' => [
            'type' => 'integer',
            'null' => false,
            'default' => null,
            'unsigned' => false
        ],
        'created' => ['type' => 'datetime', 'null' => true, 'default' => null],
        'modified' => ['type' => 'datetime', 'null' => true, 'default' => null],
        'text' => [
            'type' => 'string',
            'null' => true,
            'default' => null,
            'collate' => 'utf8_general_ci',
            'charset' => 'utf8'
        ],
        'user_id' => [
            'type' => 'integer',
            'null' => false,
            'default' => null,
            'unsigned' => false
        ],
        'time' => ['type' => 'timestamp', 'null' => true, 'default' => null],
        '_constraints' => [
            'primary' => [
                'type' => 'primary',
                'columns' => ['id']
            ]
        ],
        '_options' => [
            'charset' => 'utf8',
            'collate' => 'utf8_general_ci',
            'engine' => 'MEMORY'
        ]
    );

    public $records = [
        [
            'id' => 2,
            'time' => '2013-02-08 11:49:31',
            'text' => 'Lorem ipsum dolor sit amet',
            'user_id' => 1
        ],
        [
            'id' => 3,
            'time' => '2013-02-08 11:49:31',
            'text' => 'Lorem ipsum dolor sit amet',
            'user_id' => 1
        ],
        [
            'id' => 4,
            'time' => '2013-02-08 11:49:31',
            'text' => "<script></script>[i]italic[/i]",
            'user_id' => 1
        ],
    ];
}
