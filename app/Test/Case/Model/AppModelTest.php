<?php
	App::uses('AppModel', 'Model');

	class AppModelMock extends AppModel {

		public function setAllowedInputFields($in) {
			$this->_allowedInputFields = $in;
		}

	}

	class AppModelTest extends CakeTestCase {

		public function testFilterFields() {
			$data = ['AppModel' => ['a' => 1, 'b' => 2, 'c' => 3]];
			$filter = ['a', 'b'];
			$this->AppModel->filterFields($data, $filter);
			$expected = ['AppModel' => ['a' => 1, 'b' => 2]];
			$this->assertEquals($expected, $data);
		}

		public function testFilterFieldsClassPreset() {
			$this->AppModel = ClassRegistry::init('AppModelMock');

			$this->AppModel->setAllowedInputFields(['foo' => ['a', 'c']]);
			$data = ['AppModelMock' => ['a' => 1, 'b' => 2, 'c' => 3]];
			$this->AppModel->filterFields($data, 'foo');

			$expected = ['AppModelMock' => ['a' => 1, 'c' => 3]];
			$this->assertEquals($expected, $data);
		}

		public function testRequiredFields() {
			$data = ['a' => 1, 'b' => 2, 'c' => 3];
			$required = ['a', 'b'];
			$result = $this->AppModel->requireFields($data, $required);
			$this->assertTrue($result);

			$data = ['a' => 1, 'b' => 2, 'c' => 3];
			$required = ['a', 'b', 'd'];
			$result = $this->AppModel->requireFields($data, $required);
			$this->assertFalse($result);
		}

		public function testUnsetFields() {
			$data = ['id' => 1, 'b' => 2, 'c' => 3];
			$this->AppModel->unsetFields($data);
			$expected = ['AppModel' => ['b' => 2, 'c' => 3]];
			$this->assertEquals($expected, $data);

			$data = ['AppModel' => ['id' => 1, 'b' => 2, 'c' => 3]];
			$unset = ['id', 'b'];
			$this->AppModel->unsetFields($data, $unset);
			$expected = ['AppModel' => ['c' => 3]];
			$this->assertEquals($expected, $data);
		}

		public function testUnsetFieldsArray() {
			$data = [
				['AppModel' => ['id' => 1, 'b' => 2, 'c' => 3]],
				['AppModel' => ['id' => 2, 'b' => 3, 'c' => 4]]
			];
			$unset = ['id', 'b'];
			$this->AppModel->unsetFields($data, $unset);
			$expected = [
				['AppModel' => ['c' => 3]],
				['AppModel' => ['c' => 4]]
			];
			$this->assertEquals($expected, $data);
		}

		/**
		 * setUp method
		 *
		 * @return void
		 */
		public function setUp() {
			parent::setUp();
			$this->AppModel = ClassRegistry::init('AppModel');
		}

		/**
		 * tearDown method
		 *
		 * @return void
		 */
		public function tearDown() {
			unset($this->AppModel);

			parent::tearDown();
		}

	}
