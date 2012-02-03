<?php
/* CurrentUser Test cases generated on: 2011-05-17 05:53:43 : 1305604423*/
App::import('Component', 'CurrentUser');

class CurrentUserComponentTestCase extends CakeTestCase {
	var $fixtures = array('app.user', 'app.user_online', 'app.entry', 'app.category', 'app.smiley', 'app.smiley_code', 'app.setting', 'app.upload');
	public $name = 'Entries';

	function startTest() {
		$this->CurrentUser =& new CurrentUserComponent();
	}

	function endTest() {
		unset($this->CurrentUser);
		ClassRegistry::flush();
	}

}
?>