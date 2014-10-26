<?php

	App::uses('SaitoMarkupParser', 'Lib/Saito/Markup');

	class SaitoMarkupParserMock extends SaitoMarkupParser {

		public function parse($string, array $options = []) {
		}

	}

	class SaitoMarkupParserTest extends CakeTestCase {

		public function testCiteEmptyText() {
			$input = '';
			$result = $this->Parser->citeText($input);
			$expected = '';
			$this->assertEquals($result, $expected);
		}

		public function testCiteText() {
			$input = "123456789 123456789 123456789 123456789 123456789 123456789 123456789 123456789";
			$result = $this->Parser->citeText($input);
			$expected = "» 123456789 123456789 123456789 123456789 123456789 123456789 123456789\n» 123456789\n";
			$this->assertEquals($result, $expected);
		}

		public function setUp() {
			$Request = new CakeRequest('/');
			$Controller = new Controller($Request);
			$View = new View($Controller);
			$Helper = new Helper($View);
			$this->Parser = new SaitoMarkupParserMock($Helper, ['quote_symbol' => '»']);
		}

		public function tearDown() {
			unset($this->Parser);
		}

	}
