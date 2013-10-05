<?php

	App::uses('ApiControllerTestCase', 'Api.Lib');

	/**
	 * ApiUsersController Test Case
	 *
	 */
	class ApiCoreControllerTest extends ApiControllerTestCase {

		protected $apiRoot = 'api/v1/';

		/**
		 * Fixtures
		 *
		 * @var array
		 */
		public $fixtures = array(
			'plugin.api.entry',
			'plugin.api.category',
			'plugin.api.user',
			'plugin.api.user_online',
			'plugin.api.bookmark',
			'plugin.api.esnotification',
			'plugin.api.esevent',
			'plugin.api.ecach',
			'plugin.api.upload',
			'plugin.api.setting'
		);

		public function testBootstrap() {

			$expected = json_decode(<<<EOF
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
    "shoutbox_enabled": true,
    "subject_maxlength": 100
  },
  "user": {
    "isLoggedIn": false
  }
}
EOF
			, true);

			$result = $this->testAction(
				$this->apiRoot . 'bootstrap.json',
				[
					'method' => 'GET',
					'return' => 'contents'
				]
			);

			$result = json_decode($result, true);

			// test server_time
			$this->assertNotEmpty($result['server']['time']);
			$server_time = strtotime($result['server']['time']);
			$withinTheLastFewSeconds = $server_time > (time() - 20);
			$this->assertTrue($withinTheLastFewSeconds);
			unset($result['server']['time']);

			if (empty($result['server'])) {
				unset($result['server']);
			}

			$this->assertEqual($result, $expected);
		}

		public function testBootstrapCategoriesLoggedIn() {
			$expected = json_decode(<<<EOF
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
EOF
				, true);
			$this->generate('ApiCore');
			$this->_loginUser(3);
			$result = $this->testAction(
				$this->apiRoot . 'bootstrap.json',
				[
					'method' => 'GET',
					'return' => 'contents'
				]
			);
			$result = json_decode($result, true)['categories'];
			$this->assertEqual($result, $expected);
		}
	}
