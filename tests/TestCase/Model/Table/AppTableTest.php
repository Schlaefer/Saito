<?php

	namespace App\Test\TestCase\Model\Table;

	use Saito\Test\Model\Table\SaitoTableTestCase;

	class AppTableTest extends SaitoTableTestCase {

		public $tableClass = 'Saito\Test\Model\Table\AppTableMock';

		public $fixtures = ['app.category', 'app.entry', 'app.user'];

		public function testFilterFields() {
			$data = ['a' => 1, 'b' => 2, 'c' => 3];
			$filter = ['a', 'b'];
			$this->Table->filterFields($data, $filter);
			$expected = ['a' => 1, 'b' => 2];
			$this->assertEquals($expected, $data);
		}

		public function testFilterFieldsClassPreset() {
			$this->Table->setAllowedInputFields(['foo' => ['a', 'c']]);
			$data = ['a' => 1, 'b' => 2, 'c' => 3];
			$this->Table->filterFields($data, 'foo');

			$expected = ['a' => 1, 'c' => 3];
			$this->assertEquals($expected, $data);
		}

		public function testRequiredFields() {
			$data = ['a' => 1, 'b' => 2, 'c' => 3];
			$required = ['a', 'b'];
			$result = $this->Table->requireFields($data, $required);
			$this->assertTrue($result);

			$data = ['a' => 1, 'b' => 2, 'c' => 3];
			$required = ['a', 'b', 'd'];
			$result = $this->Table->requireFields($data, $required);
			$this->assertFalse($result);
		}

		public function testUnsetFields() {
			$data = ['id' => 1, 'b' => 2, 'c' => 3];
			$this->Table->unsetFields($data);
			$expected = ['b' => 2, 'c' => 3];
			$this->assertEquals($expected, $data);

			$data = ['id' => 1, 'b' => 2, 'c' => 3];
			$unset = ['id', 'b'];
			$this->Table->unsetFields($data, $unset);
			$expected = ['c' => 3];
			$this->assertEquals($expected, $data);
		}

		public function testUnsetFieldsArray() {
			$data = [
				['id' => 1, 'b' => 2, 'c' => 3],
				['id' => 2, 'b' => 3, 'c' => 4]
			];
			$unset = ['id', 'b'];
			$this->Table->unsetFields($data, $unset);
			$expected = [
				['c' => 3],
				['c' => 4]
			];
			$this->assertEquals($expected, $data);
		}

	}
