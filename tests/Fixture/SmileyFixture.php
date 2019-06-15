<?php

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class SmileyFixture extends TestFixture
{

    public $fields = [
        'id' => [
            'type' => 'integer',
            'null' => false,
            'default' => null,
            'unsigned' => false
        ],
        'sort' => [
            'type' => 'integer',
            'null' => false,
            'default' => '0',
            'length' => 4,
            'unsigned' => false
        ],
        'icon' => [
            'type' => 'string',
            'null' => true,
            'default' => null,
            'length' => 100,
        ],
        'image' => [
            'type' => 'string',
            'null' => true,
            'default' => null,
            'length' => 100,
        ],
        'title' => [
            'type' => 'string',
            'null' => true,
            'default' => null,
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
        ],
    ];

    public $records = [
        [
            'id' => 1,
            'sort' => 2,
            'icon' => 'smile_icon.png',
            'image' => 'smile_image.png',
            'title' => 'Smile',
        ],
        [
            'id' => 2,
            'sort' => 1,
            'icon' => 'wink.svg',
            'image' => '',
            'title' => 'Wink',
        ],
        [
            'id' => 3,
            'sort' => 3,
            'icon' => 'coffee',
            'image' => '',
            'title' => 'Coffee',
        ],
    ];
}
