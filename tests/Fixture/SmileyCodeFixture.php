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
            'collate' => 'utf8_general_ci',
            'charset' => 'utf8'
        ],
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
