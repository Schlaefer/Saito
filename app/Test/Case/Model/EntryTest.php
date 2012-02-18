<?php
/* Entry Test cases generated on: 2010-07-08 18:07:15 : 1278607395*/
App::import('Model', 'Entry');

class EntryTest extends CakeTestCase {
	var $fixtures = array('app.user', 'app.user_online', 'app.entry', 'app.category', 'app.smiley', 'app.smiley_code', 'app.setting', 'app.upload');

	public function testBeforeValidate() {

		//* save entry with text
		$entry['Entry'] = array(
				'user_id' => 3,
				'subject'	=> 'Test Subject',
				'Text'		=> 'Text Text',
				'pid'			=> '2',
			);

	}

	public function testToggle() {

		$this->Entry->id = 2;

		//* test that thread is unlocked 
		$result = $this->Entry->field('locked');
		$this->assertFalse($result);

		//* lock thread
		$this->Entry->toggle('locked');
		$result = $this->Entry->field('locked');
		$this->assertTrue($result);

		//* unlock thread again
		$this->Entry->toggle('locked');
		$result = $this->Entry->field('locked');
		$this->assertFalse($result);
	}

	public function testDeleteTree() {

		//* test thread exists before we delete it
		$result = $this->Entry->find('count', array('conditions' => array('tid' => '1')));
		$expected = 3;
		$this->assertEqual($result, $expected);

		//* try to delete subentry
		$this->Entry->id = 2; 
		$result = $this->Entry->deleteTree();
		$this->assertFalse($result);

		$result = $this->Entry->find('count', array('conditions' => array('tid' => '1')));
		$expected = 3;
		$this->assertEqual($result, $expected);

		//* try to delete thread
		$this->Entry->id = 1; 
		$result = $this->Entry->deleteTree();
		$this->assertTrue($result);

		$result = $this->Entry->find('count', array('conditions' => array('tid' => '1')));
		$expected = 0;
		$this->assertEqual($result, $expected);

		}

	function startTest($message) {
		$this->Entry =& ClassRegistry::init('Entry');
	}

	function endTest() {
		unset($this->Entry);
		ClassRegistry::flush();
	}


}
?>