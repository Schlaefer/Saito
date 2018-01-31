<?php

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class UploadFixture extends TestFixture
{

    public $name = 'Upload';

    public $fields = [
        'id' => [
            'type' => 'integer',
            'null' => false,
            'default' => null,
            'unsigned' => false
        ],
        'name' => [
            'type' => 'string',
            'null' => true,
            'default' => null,
            'length' => 200,
            'collate' => 'utf8_unicode_ci',
            'charset' => 'utf8'
        ],
        'type' => [
            'type' => 'string',
            'null' => true,
            'default' => null,
            'length' => 200,
            'collate' => 'utf8_unicode_ci',
            'charset' => 'utf8'
        ],
        'size' => [
            'type' => 'integer',
            'null' => true,
            'default' => null,
            'unsigned' => false
        ],
        'created' => ['type' => 'datetime', 'null' => true, 'default' => null],
        'modified' => ['type' => 'datetime', 'null' => true, 'default' => null],
        'user_id' => [
            'type' => 'integer',
            'null' => true,
            'default' => null,
            'unsigned' => false
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
            'name' => '3_upload_test.png',
            'type' => 'png',
            'size' => '10000',
            'user_id' => '3',
        ],
        [
            'id' => 2,
            'name' => '1_upload_test.png',
            'type' => 'jpg',
            'size' => '20000',
            'user_id' => '1',
        ]
    ];
}
