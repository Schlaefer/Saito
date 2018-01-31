<?php

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class UseronlineFixture extends TestFixture
{
    /**
     * Table name
     *
     * @var string
     */
    public $table = 'useronline';

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
        'uuid' => [
            'type' => 'string',
            'null' => false,
            'length' => 32,
            'collate' => 'utf8_unicode_ci',
            'charset' => 'utf8'
        ],
        'user_id' => [
            'type' => 'integer',
            'null' => true,
            'default' => null,
            'unsigned' => false
        ],
        'logged_in' => [
            'type' => 'boolean',
            'null' => false,
            'default' => null
        ],
        'time' => [
            'type' => 'integer',
            'null' => false,
            'default' => '0',
            'length' => 14,
            'unsigned' => false
        ],
        'created' => ['type' => 'datetime', 'null' => true, 'default' => null],
        'modified' => ['type' => 'datetime', 'null' => true, 'default' => null],
        '_constraints' => [
            'primary' => [
                'type' => 'primary',
                'columns' => ['id']
            ],
            'useronline_uuid' => ['type' => 'unique', 'columns' => 'uuid']
        ],
        '_options' => [
            'charset' => 'utf8',
            'collate' => 'utf8_unicode_ci',
            'engine' => 'MEMORY'
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
            'uuid' => 'Lorem ipsum dolor sit amet',
            'user_id' => 1,
            'logged_in' => 1,
            'time' => 1,
            'created' => '2014-05-04 07:47:53',
            'modified' => '2014-05-04 07:47:53'
        ),
    );
    */
}
