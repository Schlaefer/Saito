<?php

	App::uses('SearchesController', 'Controller');
	App::uses('SaitoControllerTestCase', 'Lib');

	class SearchesMockController extends SearchesController {

		public function sanitize($string) {
			return $this->_sanitize($string);
		}

	}

	/**
	 * SearchesController Test Case
	 *
	 */
	class SearchesControllerTest extends SaitoControllerTestCase {

		/**
		 * Fixtures
		 *
		 * @var array
		 */
		public $fixtures = array(
				'app.entry',
				'app.category',
				'app.user',
				'app.user_online',
				'app.bookmark',
				'app.esnotification',
				'app.esevent',
				'app.user_read',
				'app.upload',
				'app.setting'
		);

		/**
		 * Admin Category results should be in search results for admin
		 */
		public function testSimpleAccession() {
			$this->generate('Searches');
			$this->_loginUser(1);

			$result = $this->testAction('/searches/simple?q="Third+Thread+First_Subject"',
					['method' => 'GET', 'return' => 'vars']);
			$this->assertNotEmpty($result['results']);
		}

		/**
		 * Admin Category results shouldn't be in search results for user
		 */
		public function testSimpleNoAccession() {
			$this->generate('Searches');
			$this->_loginUser(3);

			$result = $this->testAction('/searches/simple?q="Third+Thread+First_Subject"',
					['method' => 'GET', 'return' => 'vars']);
			$this->assertEmpty($result['results']);
		}

		/**
		 * Admin Category results should be in search results for admin
		 */
		public function testAdvancedAccession() {
			$this->generate('Searches');
			$this->_loginUser(1);

			$result = $this->testAction('/searches/advanced?subject=Third+Thread+First_Subject',
					['method' => 'GET', 'return' => 'vars']);
			$this->assertNotEmpty($result['results']);
		}

		/**
		 * Admin Category results shouldn't be in search results for user
		 */
		public function testAdvancedNoAccession() {
			$this->generate('Searches');
			$this->_loginUser(3);

			$result = $this->testAction('/searches/advanced?subject=Third+Thread+First_Subject',
					['method' => 'GET', 'return' => 'vars']);
			$this->assertEmpty($result['results']);
		}

		public function testSearchAdvancedCategoryNoAccession() {
			$this->generate('Searches');
			$this->_loginUser(3);

			$this->setExpectedException('NotFoundException');
			$this->testAction('/searches/advanced?subject=foo&category=1',
				['method' => 'GET']);
		}

		public function testSearchStringSanitizer() {
			$data = 'foo bar +baz -zoo \'';
			$expected = 'foo bar +baz -zoo \\\'';

			$Searches = $this->generate('SearchesMock');
			$result = $Searches->sanitize($data);
			$this->assertEquals($expected, $result);
		}

	}
