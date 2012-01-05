<?php
/* Smily Test cases generated on: 2011-04-28 21:04:11 : 1304017691*/
App::import('Model', 'Smily');

class SmileyTestCase extends CakeTestCase {
	var $fixtures = array('app.smiley', 'app.smiley_code');

	public function testLoad() {

		// test loading into Configure
		$this->Smiley->load();
		$result = Configure::read('Saito.Smilies.smilies_all');
		$expected = array(
				array(
					'order'			=> 1,
					'icon'			=> 'wink.png',
					'image'			=> 'wink.png',
					'title'			=> 'Wink',
					'code'			=> ';)',
				),
				array(
					'order'			=> 2,
					'icon'			=> 'smile_icon.png',
					'image'			=> 'smile_image.png',
					'title'			=> 'Smile',
					'code'			=> ':-)',
				),
				array(
					'order'			=> 2,
					'icon'			=> 'smile_icon.png',
					'image'			=> 'smile_image.png',
					'title'			=> 'Smile',
					'code'			=> ';-)',
				),
			);
		$this->assertEqual($result, $expected);		
		}

	function startTest($message) {
		if(php_sapi_name() == 'cli') {
			echo "Starting ".get_class($this)."->$message()\n";
		} else {
			echo "<h3>Starting ".get_class($this)."->$message()</h3>";
		}
		
		$this->Smiley =& ClassRegistry::init('Smiley');
	}

	function endTest() {
		unset($this->Smily);
		ClassRegistry::flush();
	}
}
?>