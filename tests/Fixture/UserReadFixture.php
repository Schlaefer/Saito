<?php

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class UserReadFixture extends TestFixture
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
            'collate' => 'utf8_general_ci',
            'engine' => 'InnoDB'
        ]
    ];

    /**
     * Records
     *
     * @var array
     */
    public $records = [
        [
            'id' => 1,
            'user_id' => 9999,
            'entry_id' => 9999
        ],
    ];
}
