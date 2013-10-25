<?php
	App::uses('Shout', 'Shoutbox.Model');

	/**
	 * Shout Test Case
	 *
	 */
	class ShoutTest extends CakeTestCase {

	/**
	 * Fixtures
	 *
	 * @var array
	 */
		public $fixtures = array(
			'shout',
			'user',
		);

		public function testPush() {
			$_numberOfShouts = $this->Shout->find('count');

			$data = array(
				'Shout' => array(
					'text' => 'The text',
					'user_id' => 3
				)
			);
			$this->Shout->push($data);
			$id = $this->Shout->id;
			$result = $this->Shout->findById($id);

			$this->assertGreaterThan(time() - 60, strtotime($result['Shout']['time']));

			$result = array_intersect_key($result['Shout'], $data['Shout']);
			$this->assertEqual($data['Shout'], $result);

			$result = $this->Shout->find('count');
			$expected = $_numberOfShouts + 1;
			$this->assertEqual($result, $expected);
		}

		public function testNoRotate() {
			$model = $this->getMockForModel(
				'Shout',
				array('shift')
			);

			$data = array(
				'Shout' => array(
					'text' => 'The text',
					'user_id' => 3
				)
			);

			$_numberOfShouts = $this->Shout->find('count');
			$this->assertGreaterThanOrEqual(3, $_numberOfShouts);

			$model->maxNumberOfShouts = $_numberOfShouts + 1;

			$model->expects($this->never())
					->method('shift');
			$model->push($data);
		}

		public function testRotate() {
			$model = $this->getMockForModel(
				'Shout',
				array('shift')
			);

			$data = array(
				'Shout' => array(
					'text' => 'The text',
					'user_id' => 3
				)
			);

			$_numberOfShouts = $this->Shout->find('count');
			$this->assertGreaterThanOrEqual(3, $_numberOfShouts);

			$model->maxNumberOfShouts = $_numberOfShouts - 1;

			$model->expects($this->exactly(2))
					->method('shift')
					->will($this->returnValue(true));
			$model->push($data);
		}

		public function testShift() {
			$before = $this->Shout->find('all', array(
				'fields' => 'Shout.id',
				'order' => 'Shout.id ASC'
			));

			$result = $this->Shout->shift();
			$this->assertTrue($result);

			$after = $this->Shout->find('all',
				array(
					'fields' => 'Shout.id',
					'order' => 'Shout.id ASC'
				));

			array_shift($before);

			$this->assertEqual($after, $before);
		}

	/**
	 * setUp method
	 *
	 * @return void
	 */
		public function setUp() {
			parent::setUp();
			$this->Shout = ClassRegistry::init('Shout');
		}

	/**
	 * tearDown method
	 *
	 * @return void
	 */
		public function tearDown() {
			unset($this->Shout);

			parent::tearDown();
		}

	}
