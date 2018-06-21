<?php

namespace Installer\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class PhinxlogFixture extends TestFixture
{

    public $fields = [
        'version' => [
            'type' => 'integer',
            'null' => false,
            'default' => null,
            'unsigned' => false
        ],
        'migration_name' => [
            'type' => 'string',
            'null' => true,
            'default' => null,
        ],
        'start_time' => [
            'type' => 'datetime',
            'null' => true,
            'default' => null,
            'collate' => null,
            'comment' => ''
        ],
        'end_time' => [
            'type' => 'datetime',
            'null' => true,
            'default' => null,
            'collate' => null,
            'comment' => ''
        ],
        'user_automaticaly_mark_as_read' => [
            'type' => 'boolean',
            'null' => false,
            'default' => '0'
        ],
    ];

    public $records = [];
}
