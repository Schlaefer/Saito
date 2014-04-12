<?php
	App::uses('View', 'View');
	App::uses('Helper', 'View');
	App::uses('MapHelper', 'View/Helper');

	/**
	 * MapHelper Test Case
	 *
	 */
	class MapHelperTest extends CakeTestCase {

		protected $_users = [
			0 => [
				'User' => [
					'id' => '1',
					'username' => 'Juliet',
					'user_place_lat' => '33.9425',
					'user_place_lng' => '118.408056',
					'user_place_zoom' => 8,
					'password' => 'Downtown'
				]
			],
			1 => [
				'User' => [
					'id' => '2',
					'username' => 'James',
					'user_place_lat' => '33.8423',
					'user_place_lng' => '87.2772',
					'user_place_zoom' => 15,
					'password' => 'OfMiceAndMen'
				]
			]
		];

		/**
		 * setUp method
		 *
		 * @return void
		 */
		public function setUp() {
			parent::setUp();
			$View = new View();
			$this->Map = new MapHelper($View);
		}

		/**
		 * tearDown method
		 *
		 * @return void
		 */
		public function tearDown() {
			unset($this->Map);

			parent::tearDown();
		}

		public function testMapViewMultiple() {
			$user = $this->_users;
			$results = $this->Map->map($user);
			$this->assertTags($results, [
				'div' => [
					'class' => 'saito-usermap',
					'data-users' => 'preg:/.*/',
					'data-params' => 'preg:/.*/',
				],
				'/div'
			]);

			$results = $this->_parseResults($results);
			$expected = [
				'users' => [
					0 => [
						'id' => 1,
						'name' => 'Juliet',
						'lat' => 33.94,
						'lng' => 118.41
					],
					[
						'id' => 2,
						'name' => 'James',
						'lat' => '33.84',
						'lng' => '87.28',
					]
				],
				'params' => [
					'type' => 'world',
					'fields' => []
				]
			];
			$this->assertEquals($expected, $results);
		}

		public function testMapViewSingle() {
			$user = $this->_users[0];
			$results = $this->Map->map($user);
			$results = $this->_parseResults($results);
			$expected = [
				'users' => [
					0 => [
						'id' => 1,
						'name' => 'Juliet',
						'lat' => 33.94,
						'lng' => 118.41
					]
				],
				'params' => [
					'type' => 'single',
					'fields' => []
				]
			];
			$this->assertEquals($expected, $results);
		}

		public function testMapEdit() {
			$user = $this->_users[0];
			$results = $this->Map->map($user, ['type' => 'edit',
				'fields' => [
					'edit' => '#UserUserPlace',
					'update' => [
						'lat' => ['#UserUserPlaceLat'],
						'lng' => ['#UserUserPlaceLng'],
						'zoom' => ['#UserUserPlaceZoom']
					]
				]
			]);
			$results = $this->_parseResults($results);
			$expected = [
				'users' => [
					0 => [
						'id' => 1,
						'name' => 'Juliet',
						'lat' => 33.9425,
						'lng' => 118.408056,
						'zoom' => 8
					]
				],
				'params' => [
					'type' => 'edit',
					'fields' => [
						'edit' => '#UserUserPlace',
						'update' => [
							'lat' => ['#UserUserPlaceLat'],
							'lng' => ['#UserUserPlaceLng'],
							'zoom' => ['#UserUserPlaceZoom']
						]
					]
				]
			];
			$this->assertEquals($expected, $results);
		}

		protected function _parseResults($results) {
			preg_match('/data-users="(?P<users>.*)"\s*data-params="(?P<params>.*)"/',
				$results, $matches);
			$results = [
				'users' => json_decode(htmlspecialchars_decode($matches['users']), 1),
				'params' => json_decode(htmlspecialchars_decode($matches['params']), 1)
			];
			return $results;
		}

	}
