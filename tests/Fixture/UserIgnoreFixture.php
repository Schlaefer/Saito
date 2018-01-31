<?php

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class UserIgnoreFixture extends TestFixture
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
            'unsigned' => true
        ],
        'created' => ['type' => 'datetime', 'null' => true, 'default' => null],
        'modified' => ['type' => 'datetime', 'null' => true, 'default' => null],
        'user_id' => [
            'type' => 'integer',
            'null' => true,
            'default' => null,
            'unsigned' => false
        ],
        'blocked_user_id' => [
            'type' => 'integer',
            'null' => true,
            'default' => null,
            'unsigned' => false
        ],
        'timestamp' => [
            'type' => 'datetime',
            'null' => true,
            'default' => null
        ],
        '_constraints' => [
            'primary' => [
                'type' => 'primary',
                'columns' => ['id']
            ]
        ],
        '_options' => [
            'charset' => 'utf8',
            'collate' => 'utf8_general_ci',
            'engine' => 'InnoDB'
        ]
    ];

    /**
     * Records
     *
     * @var array
     */
    /*
public $records = array(
array(
    'id' => 1,
    'created' => '2014-08-07 17:48:57',
    'modified' => '2014-08-07 17:48:57',
    'user_id' => 1,
    'blocked_user_id' => 1,
    'timestamp' => '2014-08-07 17:48:57'
        ),
    );
    */
}
