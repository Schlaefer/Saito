<?php

App::import('Helper', 'TextH');

class TextHHelperTestCase extends CakeTestCase {

	public function testProperize() {
		
		$input = 'Jack';
		$expected	= 'Jacks';
		$result = $this->TextH->properize($input);
		$this->assertEqual($expected, $result);

		$input = 'James';
		$expected	= 'James’';
		$result = $this->TextH->properize($input);
		$this->assertEqual($expected, $result);

		$input = 'James™';
		$expected	= 'James™s';
		$result = $this->TextH->properize($input);
		$this->assertEqual($expected, $result);

		$input = 'JAMES';
		$expected	= 'JAMES’';
		$result = $this->TextH->properize($input);
		$this->assertEqual($expected, $result);

		$this->assertEqual($this->TextH->properize('Bruce'), 'Bruce’');
		$this->assertEqual($this->TextH->properize('Weiß'), 'Weiß’');
		$this->assertEqual($this->TextH->properize('Merz'), 'Merz’');

	}

	function startTest($message) {
		$this->TextH =& new TextHHelper();
		if(php_sapi_name() == 'cli') {
			echo "Starting ".get_class($this)."->$message()\n";
			}
		}

	function endTest() {
		unset($this->TextH);
		ClassRegistry::flush();
	}
}
?>
