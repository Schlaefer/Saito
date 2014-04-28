<?php

	App::uses('Ecach', 'Model');
	App::uses('SchemaCakeMysqlFixTrait', 'Lib');

	/**
	 * Ecach Test Case
	 *
	 */
	class EcachTest extends CakeTestCase {

		use SchemaCakeMysqlFixTrait;

		/**
		 * Fixtures
		 *
		 * @var array
		 */
		public $fixtures = [
			'app.ecach'
		];

		public function testKeyFieldLength() {
			App::uses('SchemaCakeMysqlFixTrait', 'Lib');
			$DS = $this->Ecach->getDataSource();
			if (stripos($DS->config['datasource'], 'MySQL') === false) {
				return;
			}

			$length = 10e6;

			$saveData = function($length) {
				$data = str_pad('', $length, 0);
				$this->Ecach->save(['key' => 'foo', 'value' => $data]);
			};

			$expectSavedLength = function($expected) {
				$result = $this->Ecach->findByKey('foo');
				$this->assertEquals($expected, strlen($result['Ecach']['value']));
			};

			$saveData($length);
			// fixture has the same problem as schema.php: CakePHP only creates a BLOB
			// for `binary` field type on MySQL
			$expectSavedLength(65535);

			// this fix is run in schema.php's after()
			$this->cakeMysqlMediumBlobFix();
			$saveData($length);
			$expectSavedLength($length);
		}

		/**
		 * setUp method
		 *
		 * @return void
		 */
		public function setUp() {
			parent::setUp();
			$this->Ecach = ClassRegistry::init('Ecach');
		}

		/**
		 * tearDown method
		 *
		 * @return void
		 */
		public function tearDown() {
			unset($this->Ecach);

			parent::tearDown();
		}

	}
