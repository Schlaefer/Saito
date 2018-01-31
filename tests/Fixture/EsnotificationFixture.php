<?php

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class EsnotificationFixture extends TestFixture
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
        'esevent_id' => [
            'type' => 'integer',
            'null' => false,
            'default' => null,
            'unsigned' => false
        ],
        'esreceiver_id' => [
            'type' => 'integer',
            'null' => false,
            'default' => null,
            'unsigned' => false
        ],
        'deactivate' => [
            'type' => 'integer',
            'null' => false,
            'default' => null,
            'length' => 8,
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

    /**
     * Records
     *
     * @var array
     */
    public $records = [
        [
            'id' => 1,
            'user_id' => 1,
            'esevent_id' => 1,
            'esreceiver_id' => 1,
            'deactivate' => 1234,
        ],
        [
            'id' => 2,
            'user_id' => 1,
            'esevent_id' => 1,
            'esreceiver_id' => 2,
            'deactivate' => 2234,
        ],
        [
            'id' => 3,
            'user_id' => 3,
            'esevent_id' => 1,
            'esreceiver_id' => 1,
            'deactivate' => 3234,
        ],
        [
            'id' => 4,
            'user_id' => 3,
            'esevent_id' => 4,
            'esreceiver_id' => 1,
            'deactivate' => 4234,
        ],
        [
            'id' => 5,
            'user_id' => 2,
            'esevent_id' => 4,
            'esreceiver_id' => 1,
            'deactivate' => 5234,
        ],
        [
            'id' => 6,
            'user_id' => 2,
            'esevent_id' => 2,
            'esreceiver_id' => 1,
            'deactivate' => 6234,
        ],
        [
            'id' => 7,
            'user_id' => 4,
            'esevent_id' => 3,
            'esreceiver_id' => 1,
            'deactivate' => 7234,
        ],
    ];
}
