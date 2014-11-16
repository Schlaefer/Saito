<?php

	use Saito\User\Auth\CategoryAuthorization;
	use Saito\User\SaitoUser;

	class CategoryAuthTest extends CakeTestCase {

		public $fixtures = [
			'app.category'
		];

		public function testGetCategoriesSelectForAccession() {
			$User = new SaitoUser(['id' => 1, 'user_type' => 'anon']);
			$this->Lib = new CategoryAuthorization($User);

			$result = $this->Lib->getAllowed('select');
			$expected = array(
				3 => 'Another Ontopic',
				2 => 'Ontopic'
			);
			$this->assertEquals($result, $expected);

			$result = $this->Lib->getAllowed();
			$expected = array(
				3 => 3,
				2 => 2
			);
			$this->assertEquals($result, $expected);
		}

	}