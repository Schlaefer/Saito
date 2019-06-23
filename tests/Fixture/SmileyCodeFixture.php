<?php

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class SmileyCodeFixture extends TestFixture
{

    public $name = 'SmileyCode';

    public $fields = [
        'id' => [
            'type' => 'integer',
            'null' => false,
            'default' => null,
            'unsigned' => false
        ],
        'smiley_id' => [
            'type' => 'integer',
            'null' => false,
            'default' => '0',
            'unsigned' => false
        ],
        'code' => [
            'type' => 'string',
            'null' => true,
            'default' => null,
            'length' => 32,
        ],
        '_constraints' => [
            'primary' => [
                'type' => 'primary',
                'columns' => ['id']
            ]
        ],
        '_options' => [
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci',
            'engine' => 'InnoDB'
        ]
    ];

    public $records = [
        [
            'id' => 1,
            'smiley_id' => 1,
            'code' => ':-)'
        ],
        [
            'id' => 2,
            'smiley_id' => 2,
            'code' => ';-)'
        ],
        [
            'id' => 3,
            'smiley_id' => 2,
            'code' => ';)'
        ],
        [
            'id' => 4,
            'smiley_id' => 3,
            'code' => '[_]P'
        ]
    ];
}
