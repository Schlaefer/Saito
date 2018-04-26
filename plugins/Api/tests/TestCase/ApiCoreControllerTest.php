<?php

namespace Api\Test;

use Api\Lib\ApiIntegrationTestCase;
use Cake\Core\Configure;

/**
 * ApiUsersController Test Case
 *
 */
class ApiCoreControllerTest extends ApiIntegrationTestCase
{

    protected $_apiRoot = 'api/v1/';

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'app.category',
        'app.entry',
        'app.esevent',
        'app.esnotification',
        'app.setting',
        'app.upload',
        'app.user',
        'app.user_block',
        'app.user_ignore',
        'app.user_online',
        'app.user_read',
        'plugin.bookmarks.bookmark'
    ];

    public function testBootstrap()
    {
        Configure::write('Saito.Settings.edit_period', 20);

        $_json = <<<EOF
{
  "categories": [
    {
      "id": 2,
      "order": 3,
      "title": "Ontopic",
      "description": "",
      "accession": 0
    },
    {
      "id": 3,
      "order": 2,
      "title": "Another Ontopic",
      "description": "",
      "accession": 0
    }
  ],
  "settings": {
    "edit_period": 20,
    "subject_maxlength": 40
  },
  "user": {
    "isLoggedIn": false
  }
}
EOF;
        $expected = json_decode($_json, true);

        $this->get($this->_apiRoot . 'bootstrap.json');
        $result = json_decode((string)$this->_response->getBody(), true);

        // test server_time
        $this->assertNotEmpty($result['server']['time']);
        $_serverTime = strtotime($result['server']['time']);
        $withinTheLastFewSeconds = $_serverTime > (time() - 20);
        $this->assertTrue($withinTheLastFewSeconds);
        unset($result['server']['time']);

        if (empty($result['server'])) {
            unset($result['server']);
        }

        $this->assertEquals($expected, $result);
    }

    public function testBootstrapCategoriesLoggedIn()
    {
        $_json = <<<EOF
 [
    {
      "id": 2,
      "order": 3,
      "title": "Ontopic",
      "description": "",
      "accession": 0
    },
    {
      "id": 3,
      "order": 2,
      "title": "Another Ontopic",
      "description": "",
      "accession": 0
    },
    {
      "id": 4,
      "order": 4,
      "title": "Offtopic",
      "description": "",
      "accession": 1
    },
    {
      "id": 5,
      "order": 4,
      "title": "Trash",
      "description": "",
      "accession": 1
    }
]
EOF;
        $expected = json_decode($_json, true);
        $this->_loginUser(3);
        $this->get($this->_apiRoot . 'bootstrap.json');
        $result = (string)$this->_response->getBody();
        $result = json_decode($result, true)['categories'];
        $this->assertEquals($expected, $result);
    }
}
