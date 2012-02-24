<?php

	App::uses('Smily', 'Model');

	class SmileyTest extends CakeTestCase {

		public $fixtures = array( 'app.smiley', 'app.smiley_code' );

		public function testLoad() {

			// test loading into Configure
			$this->Smiley->load();
			$result = Configure::read('Saito.Smilies.smilies_all');
			$expected = array(
					array(
							'order' => 1,
							'icon' => 'wink.png',
							'image' => 'wink.png',
							'title' => 'Wink',
							'code' => ';)',
					),
					array(
							'order' => 2,
							'icon' => 'smile_icon.png',
							'image' => 'smile_image.png',
							'title' => 'Smile',
							'code' => ':-)',
					),
					array(
							'order' => 2,
							'icon' => 'smile_icon.png',
							'image' => 'smile_image.png',
							'title' => 'Smile',
							'code' => ';-)',
					),
			);
			$this->assertEqual($result, $expected);
		}

		function setUp() {
			$this->Smiley = ClassRegistry::init('Smiley');
		}

	}

?>