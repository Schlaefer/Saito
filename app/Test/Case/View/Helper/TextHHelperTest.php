<?php

	App::uses('Controller', 'Controller');
	App::uses('View', 'View');
	App::uses('TextHHelper', 'View/Helper');

	class TextHHelperTest extends CakeTestCase {

		public function testProperize() {

			$input = 'Jack';
			$expected = 'Jacks';
			$result = $this->TextH->properize($input);
			$this->assertEqual($expected, $result);

			$input = 'James';
			$expected = 'James’';
			$result = $this->TextH->properize($input);
			$this->assertEqual($expected, $result);

			$input = 'James™';
			$expected = 'James™s';
			$result = $this->TextH->properize($input);
			$this->assertEqual($expected, $result);

			$input = 'JAMES';
			$expected = 'JAMES’';
			$result = $this->TextH->properize($input);
			$this->assertEqual($expected, $result);

			$this->assertEqual($this->TextH->properize('Bruce'), 'Bruce’');
			$this->assertEqual($this->TextH->properize('Weiß'), 'Weiß’');
			$this->assertEqual($this->TextH->properize('Merz'), 'Merz’');
		}

		public function setUp() {
			parent::setUp();

			$Controller = new Controller();
			$View = new View($Controller);
			$this->TextH = new TextHHelper($View);
		}

		public function tearDown() {
			parent::tearDown();
			unset($this->TextH);
			ClassRegistry::flush();
		}

	}

?>
