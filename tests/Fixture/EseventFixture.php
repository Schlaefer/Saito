<?php

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class EseventFixture extends TestFixture
{

    /**
     * Fields
     *
     * @var array
     */
    public $fields = array(
        'id' => [
            'type' => 'integer',
            'null' => false,
            'default' => null,
            'unsigned' => false
        ],
        'subject' => [
            'type' => 'integer',
            'null' => false,
            'default' => null,
            'unsigned' => false
        ],
        'event' => [
            'type' => 'integer',
            'null' => false,
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
    );

    /**
     * Records
     *
     * @var array
     */
    public $records = array(
        array(
            'id' => 1,
            'subject' => 1,
            'event' => 1
        ),
        array(
            'id' => 2,
            'subject' => 1,
            'event' => 2
        ),
        array(
            'id' => 3,
            'subject' => 2,
            'event' => 1
        ),
        array(
            'id' => 4,
            'subject' => 1,
            'event' => 3
        ),
    );
}
