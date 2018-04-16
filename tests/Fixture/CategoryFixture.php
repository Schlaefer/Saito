<?php

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class CategoryFixture extends TestFixture
{

    public $fields = [
        'id' => [
            'type' => 'integer',
            'null' => false,
            'default' => null,
            'unsigned' => false
        ],
        'category_order' => [
            'type' => 'integer',
            'null' => false,
            'default' => '0',
            'unsigned' => false
        ],
        'category' => [
            'type' => 'string',
            'null' => true,
            'default' => null,
            'collate' => 'utf8_unicode_ci',
            'charset' => 'utf8'
        ],
        'description' => [
            'type' => 'string',
            'null' => true,
            'default' => null,
            'collate' => 'utf8_unicode_ci',
            'charset' => 'utf8'
        ],
        'accession' => [
            'type' => 'integer',
            'null' => false,
            'default' => '0',
            'length' => 4,
            'unsigned' => false
        ],
        'accession_new_thread' => [
            'type' => 'integer',
            'null' => false,
            'default' => 1,
            'length' => 4,
            'unsigned' => false
        ],
        'accession_new_posting' => [
            'type' => 'integer',
            'null' => false,
            'default' => 1,
            'length' => 4,
            'unsigned' => false
        ],
        'thread_count' => [
            'type' => 'integer',
            'null' => false,
            'default' => '0',
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
            'category_order' => 1,
            'category' => 'Admin',
            'description' => '',
            'accession' => 2,
            'accession_new_thread' => 2,
            'accession_new_posting' => 2,
            'thread_count' => 1
        ],
        [
            'id' => 2,
            'category_order' => 3,
            'category' => 'Ontopic',
            'description' => '',
            'accession' => 0, // !important
            'accession_new_thread' => 1,
            'accession_new_posting' => 1,
            'thread_count' => 3
        ],
        [
            'id' => 3,
            'category_order' => 2,
            'category' => 'Another Ontopic',
            'description' => '',
            'accession' => 0,
            'accession_new_thread' => 1,
            'accession_new_posting' => 1,
        ],
        [
            'id' => 4,
            'category_order' => 4,
            'category' => 'Offtopic',
            'description' => '',
            'accession' => 1,
            'accession_new_thread' => 2,
            'accession_new_posting' => 1,
            'thread_count' => 2
        ],
        [
            'id' => 5,
            'category_order' => 4,
            'category' => 'Trash',
            'description' => '',
            'accession' => 1,
            'accession_new_thread' => 1,
            'accession_new_posting' => 1,
        ],
    ];
}
