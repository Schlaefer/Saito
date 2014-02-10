<?php

	App::uses('Properize', 'Lib');

	class ProperizeTest extends CakeTestCase {

		public function testProperizeEng() {
			Properize::setLanguage('eng');

			$input = 'Jack';
			$expected = 'Jack’s';
			$result = Properize::prop($input);
			$this->assertEqual($expected, $result);

			$input = 'James';
			$expected = 'James’';
			$result = Properize::prop($input);
			$this->assertEqual($expected, $result);

			$input = 'James™';
			$expected = 'James™’s';
			$result = Properize::prop($input);
			$this->assertEqual($expected, $result);

			$input = 'JAMES';
			$expected = 'JAMES’';
			$result = Properize::prop($input);
			$this->assertEqual($expected, $result);
		}

		public function testProperizeDeu() {
			Properize::setLanguage('deu');

			$input = 'Jack';
			$expected = 'Jacks';
			$result = Properize::prop($input);
			$this->assertEqual($expected, $result);

			$input = 'James';
			$expected = 'James’';
			$result = Properize::prop($input);
			$this->assertEqual($expected, $result);

			$input = 'James™';
			$expected = 'James™s';
			$result = Properize::prop($input);
			$this->assertEqual($expected, $result);

			$input = 'JAMES';
			$expected = 'JAMES’';
			$result = Properize::prop($input);
			$this->assertEqual($expected, $result);

			$this->assertEqual(Properize::prop('Bruce'), 'Bruce’');
			$this->assertEqual(Properize::prop('Weiß'), 'Weiß’');
			$this->assertEqual(Properize::prop('Merz'), 'Merz’');
		}

	}
