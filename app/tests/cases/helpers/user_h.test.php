<?php
/* UserH Test cases generated on: 2011-05-08 10:48:46 : 1304844526*/
App::import('Helper', 'UserH');

class UserHHelperTestCase extends CakeTestCase {
	var $fixtures = array('app.setting');

	public function testUserRank() {

		$_userranks_show 	= Configure::read('Saito.Settings.userranks_show');
		$_userranks_ranks = Configure::read('Saito.Settings.userranks_ranks');

		Configure::write('Saito.Settings.userranks_show', '1');
		Configure::write('Saito.Settings.userranks_ranks', '10=Castaway|20=Other|30=Dharma|100=Jacob');

		$expected = 'Castaway';
		$result		= $this->UserH->userRank(0);
		$this->assertEqual($expected, $result);

		$expected = 'Castaway';
		$result		= $this->UserH->userRank(10);
		$this->assertEqual($expected, $result);

		$expected = 'Other';
		$result		= $this->UserH->userRank(11);
		$this->assertEqual($expected, $result);

		$expected = 'Jacob';
		$result		= $this->UserH->userRank(99);
		$this->assertEqual($expected, $result);

		$expected = 'Jacob';
		$result		= $this->UserH->userRank(100);
		$this->assertEqual($expected, $result);

		$expected = 'Jacob';
		$result		= $this->UserH->userRank(101);
		$this->assertEqual($expected, $result);

		Configure::write('Saito.Settings.userranks_show', $_userranks_show);
		Configure::write('Saito.Settings.userranks_ranks', $_userranks_ranks);

		}

	function startTest() {
		$this->UserH =& new UserHHelper();
	}

	function endTest() {
		unset($this->UserH);
		ClassRegistry::flush();
	}

}
?>